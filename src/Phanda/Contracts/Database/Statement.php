<?php

namespace Phanda\Contracts\Database;

interface Statement
{

    /**
     * Executes the given statement.
     *
     * @return bool
     */
    public function execute(): bool;

    /**
     * Binds a value to the given statement
     *
     * @param string|int $column name or param position to be bound
     * @param mixed $value The value to bind to variable in query
     * @return Statement
     */
    public function bindValue($column, $value): Statement;

}