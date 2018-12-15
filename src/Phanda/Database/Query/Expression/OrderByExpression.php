<?php

namespace Phanda\Database\Query\Expression;

use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Database\ValueBinder;

class OrderByExpression implements ExpressionContract
{

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