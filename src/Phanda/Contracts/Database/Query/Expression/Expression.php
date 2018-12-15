<?php

namespace Phanda\Contracts\Database\Query\Expression;

use Phanda\Database\ValueBinder;

interface Expression
{
    /**
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder);

    /**
     * @param callable $visitor
     * @return $this
     */
    public function traverse(callable $visitor);

}