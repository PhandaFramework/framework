<?php

namespace Phanda\Contracts\Database;

interface Statement
{
    /**
     * Used to denote that numerical indexes should be used in a fetch call
     */
    const FETCH_TYPE_NUMERIC = 'num';

    /**
     * Used to denote that named arrays should be returned in a fetch call
     */
    const FETCH_TYPE_ASSOC = 'assoc';

    /**
     * Used to denote that named arrays should be returned in a fetch call
     */
    const FETCH_TYPE_OBJ = 'obj';

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

    /**
     * Binds a set of values to statement object with corresponding type
     *
     * @param array $params list of values to be bound
     * @return void
     */
    public function bindParams(array $params);

    /**
     * Closes the current cursor on the database.
     *
     * You should not have to call this as it is called automatically
     * internally. Used to optimise calls to database by cleaning
     * current queries, etc.
     *
     * @return Statement
     */
    public function closeCursor(): Statement;

    /**
     * Gets the count of columns in this statement
     *
     * @return int
     */
    public function getColumnCount(): int;

    /**
     * Gets the count of rows in this statement
     *
     * @return int
     */
    public function getRowCount(): int;

    /**
     * Gets the latest primary key that's been inserted
     *
     * @param null|string $table
     * @param null|string $column
     * @return string
     */
    public function getLastInsertId($table = null, $column = null): string;

    /**
     * Gets the last error code that occurred during execution of this
     * statement.
     *
     * @return string|int
     */
    public function getLastErrorCode();

    /**
     * Gets the last error and the information associated with it
     * during the execution of this statement
     *
     * @return array
     */
    public function getLastErrorInfo();

    /**
     * Gets the next row after executing this statement
     *
     * @param string $type
     * @return array|bool
     */
    public function fetch($type = self::FETCH_TYPE_ASSOC);

    /**
     * Gets all the rows returned by executing this statement
     *
     * @param string $type
     * @return array
     */
    public function fetchAll($type = self::FETCH_TYPE_ASSOC): array;

    /**
     * Returns the number of affected rows since last execution
     *
     * @return int
     */
    public function count(): int;

}