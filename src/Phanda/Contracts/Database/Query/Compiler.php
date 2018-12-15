<?php

namespace Phanda\Contracts\Database\Query;

use Phanda\Database\ValueBinder;

interface Compiler
{
    /**
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return \Closure|string
     */
    public function compile(Query $query, ValueBinder $valueBinder);
}