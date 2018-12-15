<?php

namespace Phanda\Database\Query\Expression;

use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Database\ValueBinder;

class OrderByExpression extends QueryExpression
{
    /**
     * OrderByExpression constructor.
     *
     * @param array|string|ExpressionContract $conditions
     * @param string $conjunction
     */
    public function __construct($conditions = [], $conjunction = "")
    {
        parent::__construct($conditions, $conjunction);
    }

    /**
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder)
    {
        $order = [];
        foreach ($this->conditions as $k => $direction) {
            if ($direction instanceof ExpressionContract) {
                $direction = $direction->toSql($valueBinder);
            }

            $order[] = is_numeric($k) ? $direction : sprintf('%s %s', $k, $direction);
        }

        return sprintf('ORDER BY %s', implode(', ', $order));
    }

    /**
     * @param array $orders
     * @return QueryExpression
     */
    protected function addConditionArray(array $orders): QueryExpression
    {
        $this->conditions = array_merge($this->conditions, $orders);
    }
}