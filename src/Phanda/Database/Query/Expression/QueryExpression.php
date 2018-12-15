<?php

namespace Phanda\Database\Query\Expression;

use Countable;
use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Database\ValueBinder;

class QueryExpression implements ExpressionContract, Countable
{

    /**
     * @var string
     */
    protected $conjunction;

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * QueryExpression constructor.
     * @param array|string|ExpressionContract $conditions
     * @param string $conjunction
     */
    public function __construct($conditions = [], $conjunction = "AND")
    {
        $this->setConjunction($conjunction);

        if (!empty($conditions)) {
            $this->addConditions($conditions);
        }
    }

    /**
     * Sets the conjunction for this query expression. I.e 'AND', 'OR', etc
     *
     * @param $conjunction
     * @return QueryExpression
     */
    public function setConjunction($conjunction): QueryExpression
    {
        $this->conjunction = strtoupper($conjunction);
        return $this;
    }

    /**
     * Gets the conjunction for the current query expression
     *
     * @return string
     */
    public function getConjunction()
    {
        return $this->conjunction;
    }

    /**
     * Adds conditions to the current query expression
     *
     * @param array|string|ExpressionContract $conditions
     * @return QueryExpression
     */
    public function addConditions($conditions): QueryExpression
    {
        if (is_string($conditions)) {
            $this->conditions[] = $conditions;
            return $this;
        }

        if ($conditions instanceof ExpressionContract) {
            $this->conditions[] = $conditions;
            return $this;
        }

        return $this->addConditionArray($conditions);
    }

    /**
     * Adds a condition to the expression with the '=' comparison
     *
     * @param string|ExpressionContract $field
     * @param mixed $value
     * @return QueryExpression
     */
    public function equal($field, $value): QueryExpression
    {
        return $this->addConditions(new ComparisonExpression($field, $value, '='));
    }

    /**
     * Adds a condition to the expression with the '!=' comparison
     *
     * @param string|ExpressionContract $field
     * @param mixed $value
     * @return QueryExpression
     */
    public function notEqual($field, $value): QueryExpression
    {
        return $this->addConditions(new ComparisonExpression($field, $value, '!='));
    }

    /**
     * Adds a condition to the expression with the '>' comparison
     *
     * @param string|ExpressionContract $field
     * @param mixed $value
     * @return QueryExpression
     */
    public function greaterThan($field, $value): QueryExpression
    {
        return $this->addConditions(new ComparisonExpression($field, $value, '>'));
    }

    /**
     * Adds a condition to the expression with the '>=' comparison
     *
     * @param string|ExpressionContract $field
     * @param mixed $value
     * @return QueryExpression
     */
    public function greaterThanOrEqual($field, $value): QueryExpression
    {
        return $this->addConditions(new ComparisonExpression($field, $value, '>='));
    }

    /**
     * Adds a condition to the expression with the '<' comparison
     *
     * @param string|ExpressionContract $field
     * @param mixed $value
     * @return QueryExpression
     */
    public function lessThan($field, $value): QueryExpression
    {
        return $this->addConditions(new ComparisonExpression($field, $value, '<'));
    }

    /**
     * Adds a condition to the expression with the '<=' comparison
     *
     * @param string|ExpressionContract $field
     * @param mixed $value
     * @return QueryExpression
     */
    public function lessThanOrEqual($field, $value): QueryExpression
    {
        return $this->addConditions(new ComparisonExpression($field, $value, '<='));
    }

    /**
     * Adds a condition to the expression checking if field is null
     *
     * @param string|ExpressionContract $field
     * @return QueryExpression
     */
    public function isNull($field): QueryExpression
    {
        if (!$field instanceof ExpressionContract) {
            $field = new IdentifierExpression($field);
        }

        return $this->addConditions(new UnaryExpression('IS NULL', $field, UnaryExpression::SUFFIX));
    }

    /**
     * Adds a condition to the expression checking if field is not null
     *
     * @param string|ExpressionContract $field
     * @return QueryExpression
     */
    public function isNotNull($field): QueryExpression
    {
        if (!$field instanceof ExpressionContract) {
            $field = new IdentifierExpression($field);
        }

        return $this->addConditions(new UnaryExpression('IS NOT NULL', $field, UnaryExpression::SUFFIX));
    }

    /**
     * Adds an array of conditions to the current query expressions conditions
     *
     * @param array $conditions
     * @return QueryExpression
     */
    protected function addConditionArray(array $conditions): QueryExpression
    {
        $operators = ['and', 'or', 'xor'];

        foreach ($conditions as $key => $condition) {
            $isKeyNumeric = is_numeric($key);

            if (is_callable($condition)) {
                $expression = new static([]);
                $condition = $condition($expression, $this);
            }

            if ($isKeyNumeric && empty($condition)) {
                continue;
            }

            $isArray = is_array($condition);
            $isOperator = in_array(strtolower($condition), $operators);
            $isNot = strtolower($condition) === 'not';

            if (($isOperator || $isNot) && ($isArray || $condition instanceof Countable) && count($condition) === 0) {
                continue;
            }

            if ($isKeyNumeric && $condition instanceof ExpressionContract) {
                $this->conditions[] = $condition;
                continue;
            }

            if ($isKeyNumeric && is_string($condition)) {
                $this->conditions[] = $condition;
                continue;
            }

            if ($isKeyNumeric && $isArray || $isOperator) {
                $this->conditions[] = new static($condition, $isKeyNumeric ? 'AND' : $key);
                continue;
            }

            if ($isNot) {
                $this->conditions[] = new UnaryExpression('NOT', new static($condition));
                continue;
            }

            if (!$isKeyNumeric) {
                $this->conditions[] = $this->parseCondition($key, $condition);
            }
        }

        return $this;
    }

    /**
     * Parses a condition and returns the expression
     *
     * @param $field
     * @param $value
     * @return ComparisonExpression|UnaryExpression
     */
    protected function parseCondition($field, $value)
    {
        $operator = '=';
        $expression = $field;
        $parts = explode(' ', trim($field), 2);

        if (count($parts) > 1) {
            list($expression, $operator) = $parts;
        }

        $operator = strtolower(trim($operator));

        $multiple = false;

        if (in_array($operator, ['in', 'not in'])) {
            $operator = $operator === '=' ? 'IN' : $operator;
            $operator = $operator === '!=' ? 'NOT IN' : $operator;
            $multiple = true;
        }

        if ($multiple) {
            $value = $value instanceof ExpressionContract ? $value : (array)$value;
        }

        if ($operator === 'is' && $value === null) {
            return new UnaryExpression(
                'IS NULL',
                new IdentifierExpression($expression),
                UnaryExpression::PREFIX
            );
        }

        if ($operator === 'is not' && $value === null) {
            return new UnaryExpression(
                'IS NOT NULL',
                new IdentifierExpression($expression),
                UnaryExpression::SUFFIX
            );
        }

        if ($operator === 'is' && $value !== null) {
            $operator = '=';
        }

        if ($operator === 'is not' && $value !== null) {
            $operator = '!=';
        }

        return new ComparisonExpression($expression, $value, $operator, $multiple);
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        // TODO: Implement count() method.
    }

    /**
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder)
    {
        // TODO: Implement toSql() method.
    }

    /**
     * @param callable $visitor
     * @return $this
     */
    public function traverse(callable $visitor)
    {
        // TODO: Implement traverse() method.
    }
}