<?php

namespace Phanda\Database\Query\Expression;

use BadMethodCallException;
use Countable;
use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Contracts\Database\Query\Query;
use Phanda\Database\ValueBinder;
use Phanda\Support\PhandArr;

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
     * Adds a condition to the expression with the 'LIKE' comparison
     *
     * @param string|ExpressionContract $field
     * @param mixed $value
     * @return QueryExpression
     */
    public function like($field, $value): QueryExpression
    {
        return $this->addConditions(new ComparisonExpression($field, $value, 'LIKE'));
    }

    /**
     * Adds a condition to the expression with the 'NOT LIKE' comparison
     *
     * @param string|ExpressionContract $field
     * @param mixed $value
     * @return QueryExpression
     */
    public function notLike($field, $value): QueryExpression
    {
        return $this->addConditions(new ComparisonExpression($field, $value, 'NOT LIKE'));
    }

    /**
     * Adds a condition to the expression with the 'IN (values)' comparison
     *
     * @param string|ExpressionContract $field
     * @param mixed $values
     * @return QueryExpression
     */
    public function in($field, $values): QueryExpression
    {
        $values = $values instanceof ExpressionContract ? $values : PhandArr::makeArray($values);
        return $this->addConditions(new ComparisonExpression($field, $values, 'IN', true));
    }

    /**
     * Adds a condition to the expression with the 'NOT IN (values)' comparison
     *
     * @param string|ExpressionContract $field
     * @param mixed $values
     * @return QueryExpression
     */
    public function notIn($field, $values): QueryExpression
    {
        $values = $values instanceof ExpressionContract ? $values : PhandArr::makeArray($values);
        return $this->addConditions(new ComparisonExpression($field, $values, 'NOT IN', true));
    }

    /**
     * Adds a condition to the expression performing a 'CASE' (if,then,else,etc) operation
     *
     * @param $conditions
     * @param array $values
     * @return QueryExpression
     */
    public function addCase($conditions, $values = []): QueryExpression
    {
        return $this->addConditions(new CaseExpression($conditions, $values));
    }

    /**
     * Adds a new condition to the expression object in the form "EXISTS (...)".
     *
     * @param ExpressionContract $expression
     * @return QueryExpression
     */
    public function exists(ExpressionContract $expression): QueryExpression
    {
        return $this->addConditions(new UnaryExpression('EXISTS', $expression, UnaryExpression::PREFIX));
    }

    /**
     * Adds a new condition to the expression object in the form "NOT EXISTS (...)".
     *
     * @param ExpressionContract $expression
     * @return QueryExpression
     */
    public function notExists(ExpressionContract $expression): QueryExpression
    {
        return $this->addConditions(new UnaryExpression('NOT EXISTS', $expression, UnaryExpression::PREFIX));
    }

    /**
     * Adds a new condition to the expression with the form 'field BETWEEN min AND max'
     *
     * @param string|ExpressionContract $field
     * @param mixed $min
     * @param mixed $max
     * @return QueryExpression
     */
    public function between($field, $min, $max): QueryExpression
    {
        return $this->addConditions(new BetweenExpression($field, $min, $max));
    }


    /**
     * Creates a new expression joined to the current with the 'AND' conjunction
     *
     * @param string|array|ExpressionContract $conditions
     * @return QueryExpression
     */
    public function andQuery($conditions): QueryExpression
    {
        if (is_callable($conditions)) {
            return $conditions(new static());
        }

        return new static($conditions);
    }

    /**
     * Creates a new expression joined to the current with the 'OR' conjunction
     *
     * @param string|array|ExpressionContract $conditions
     * @return QueryExpression
     */
    public function orQuery($conditions): QueryExpression
    {
        if (is_callable($conditions)) {
            return $conditions(new static(), 'OR');
        }

        return new static($conditions, 'OR');
    }

    /**
     * Adds a new set of conditions to this level of the tree and negates
     * the final result by prefixing with a NOT
     *
     * @param $conditions
     * @return QueryExpression
     */
    public function not($conditions): QueryExpression
    {
        return $this->addConditions(['NOT' => $conditions]);
    }

    /**
     * Builds an equal expression, by wrapping in IdentifierExpressions if needed
     *
     * @param $left
     * @param $right
     * @return QueryExpression
     */
    public function equalFields($left, $right): QueryExpression
    {
        $wrapIdentifier = function ($field) {
            if ($field instanceof ExpressionContract) {
                return $field;
            }

            return new IdentifierExpression($field);
        };

        return $this->equal($wrapIdentifier($left), $wrapIdentifier($right));
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
            $isOperator = in_array(strtolower($key), $operators);
            $isNot = strtolower($key) === 'not';

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
        return count($this->conditions);
    }

    /**
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder)
    {
        $len = $this->count();
        if ($len === 0) {
            return '';
        }
        $conjunction = $this->conjunction;
        $template = ($len === 1) ? '%s' : '(%s)';
        $parts = [];
        foreach ($this->conditions as $part) {
            if ($part instanceof Query) {
                $part = '(' . $part->toSql($valueBinder) . ')';
            } elseif ($part instanceof ExpressionContract) {
                $part = $part->toSql($valueBinder);
            }
            if (strlen($part)) {
                $parts[] = $part;
            }
        }

        return sprintf($template, implode(" $conjunction ", $parts));
    }

    /**
     * @param callable $visitor
     * @return $this
     */
    public function traverse(callable $visitor)
    {
        foreach ($this->conditions as $c) {
            if ($c instanceof ExpressionContract) {
                $visitor($c);
                $c->traverse($visitor);
            }
        }

        return $this;
    }

    /**
     * Iterates over the conditions of this expression, and executes a callback
     *
     * @param callable $visitor
     * @return $this
     */
    public function iterateParts(callable $visitor)
    {
        $parts = [];
        foreach ($this->conditions as $k => $condition) {
            $key =& $k;
            $part = $visitor($condition, $key);
            if ($part !== null) {
                $parts[$key] = $part;
            }
        }
        $this->conditions = $parts;

        return $this;
    }

    /**
     * Checks if a callable is accepted by this expression
     *
     * @param mixed $c
     * @return bool
     */
    public function isCallable($c): bool
    {
        if (is_string($c)) {
            return false;
        }
        if (is_object($c) && is_callable($c)) {
            return true;
        }

        return is_array($c) && isset($c[0]) && is_object($c[0]) && is_callable($c);
    }

    /**
     * Checks if the current expression has nested expressions within the conditions
     *
     * @return bool
     */
    public function hasNestedExpression(): bool
    {
        foreach ($this->conditions as $condition) {
            if ($condition instanceof ExpressionContract) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clone this object and its subtree of expressions.
     *
     * @return void
     */
    public function __clone()
    {
        foreach ($this->conditions as $i => $condition) {
            if ($condition instanceof ExpressionContract) {
                $this->conditions[$i] = clone $condition;
            }
        }
    }

    /**
     * A helper for calling 'and()' or 'or()' as these are taken in php.
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (in_array($method, ['and', 'or'])) {
            return call_user_func_array([$this, $method . 'Query'], $args);
        }

        throw new BadMethodCallException(sprintf('Method %s does not exist', $method));
    }
}