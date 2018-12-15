<?php

namespace Phanda\Database\Query\Expression;

use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Database\ValueBinder;

class CaseExpression implements ExpressionContract
{

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var string|array|ExpressionContract|null
     */
    protected $elseValue;

    /**
     * CaseExpression constructor.
     * @param array|ExpressionContract $conditions
     * @param array|ExpressionContract $values
     */
    public function __construct($conditions = [], $values = [])
    {
        if (!empty($conditions)) {
            $this->addConditions($conditions, $values);
        }

        if (is_array($conditions) && is_array($values) && count($values) > count($conditions)) {
            end($values);
            $key = key($values);
            $this->addElseValue($values[$key]);
        }
    }

    /**
     * Adds conditions to this expression
     *
     * @param array|ExpressionContract $conditions
     * @param array|ExpressionContract $values
     * @return CaseExpression
     */
    public function addConditions($conditions = [], $values = []): CaseExpression
    {
        if (!is_array($conditions)) {
            $conditions = [$conditions];
        }

        if (!is_array($values)) {
            $values = [$values];
        }

        $this->addExpressions($conditions, $values);

        return $this;
    }

    /**
     * Adds the expressions to the values array, and performs the necessary conversions
     *
     * @param array|ExpressionContract $conditions
     * @param array|ExpressionContract $values
     */
    protected function addExpressions($conditions, $values)
    {
        $rawValues = array_values($values);
        $keyValues = array_keys($values);

        foreach ($conditions as $key => $condition) {
            $numericKey = is_numeric($key);

            if ($numericKey && empty($condition)) {
                continue;
            }

            if (!$condition instanceof ExpressionContract) {
                continue;
            }

            $this->conditions[] = $condition;
            $value = isset($rawValues[$key]) ? $rawValues[$key] : 1;

            if ($value === 'literal') {
                $value = $keyValues[$key];
                $this->values[] = $value;
                continue;
            }

            if ($value === 'identifier') {
                $value = new IdentifierExpression($keyValues[$key]);
                $this->values[] = $value;
                continue;
            }

            if ($value instanceof ExpressionContract) {
                $this->values[] = $value;
                continue;
            }

            $this->values[] = ['value' => $value];
        }
    }

    /**
     * Adds the else value to the expression
     *
     * @param mixed $value
     * @return CaseExpression
     */
    protected function addElseValue($value): CaseExpression
    {
        if (is_array($value)) {
            end($value);
            $value = key($value);
        }

        $value = ['value' => $value];
        $this->elseValue = $value;

        return $this;
    }

    /**
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder)
    {
        $parts = [];
        $parts[] = 'CASE';
        foreach ($this->conditions as $key => $part) {
            $value = $this->values[$key];
            $parts[] = 'WHEN ' . $this->compile($part, $valueBinder) . ' THEN ' . $this->compile($value, $valueBinder);
        }
        if ($this->elseValue !== null) {
            $parts[] = 'ELSE';
            $parts[] = $this->compile($this->elseValue, $valueBinder);
        }
        $parts[] = 'END';

        return implode(' ', $parts);
    }

    /**
     * Compiles a part of the expression into a placeholder token in the query
     *
     * @param array|string|ExpressionContract $part
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function compile($part, ValueBinder $valueBinder)
    {
        if ($part instanceof ExpressionContract) {
            $part = $part->toSql($valueBinder);
        } elseif (is_array($part)) {
            $placeholder = $valueBinder->generatePlaceholderToken('param');
            $valueBinder->bind($placeholder, $part['value']);
            $part = $placeholder;
        }

        return $part;
    }

    /**
     * @param callable $visitor
     * @return $this
     */
    public function traverse(callable $visitor)
    {
        foreach (['conditions', 'values'] as $part) {
            foreach ($this->{$part} as $condition) {
                if ($condition instanceof ExpressionContract) {
                    $visitor($condition);
                    $condition->traverse($visitor);
                }
            }
        }

        if ($this->elseValue instanceof ExpressionContract) {
            $visitor($this->elseValue);
            $this->elseValue->traverse($visitor);
        }

        return $this;
    }
}