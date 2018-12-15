<?php

namespace Phanda\Database\Query\Expression;

use Phanda\Contracts\Database\Query\Expression\Field as FieldContract;
use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Database\ValueBinder;

class OrderClauseExpression implements ExpressionContract, FieldContract
{

    /**
     * @var string|ExpressionContract
     */
    protected $field;

    /**
     * @var string
     */
    protected $orderDirection;

    /**
     * OrderClauseExpression constructor.
     *
     * @param string|ExpressionContract $field
     * @param string $orderDirection
     */
    public function __construct($field, string $orderDirection = 'DESC')
    {
        $this->setFieldName($field);
        $this->setOrderDirection($orderDirection);
    }

    /**
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder)
    {
        $field = $this->field;
        if ($field instanceof ExpressionContract) {
            $field = $field->toSql($valueBinder);
        }

        return sprintf('%s %s', $field, $this->orderDirection);
    }

    /**
     * @param callable $visitor
     * @return $this
     */
    public function traverse(callable $visitor)
    {
        if ($this->field instanceof ExpressionContract) {
            $visitor($this->field);
            $this->field->traverse($visitor);
        }
    }

    /**
     * Sets the fields name
     *
     * @param $field
     * @return FieldContract
     */
    public function setFieldName($field): FieldContract
    {
        $this->field = $field;
        return $this;
    }

    /**
     * Gets the fields name
     *
     * @return mixed
     */
    public function getFieldName()
    {
        return $this->field;
    }

    /**
     * Sets the order direction of this order clause
     *
     * @param string $orderDirection
     * @return OrderClauseExpression
     */
    public function setOrderDirection(string $orderDirection): OrderClauseExpression
    {
        $this->orderDirection = strtolower($orderDirection) === 'asc' ? "ASC" : "DESC";
        return $this;
    }

    /**
     * Gets the order direction of this order clause
     *
     * @return string
     */
    public function getOrderDirection(): string
    {
        return $this->orderDirection;
    }

    /**
     * Create a deep clone of the order clause.
     *
     * @return void
     */
    public function __clone()
    {
        if ($this->field instanceof ExpressionContract) {
            $this->field = clone $this->field;
        }
    }
}