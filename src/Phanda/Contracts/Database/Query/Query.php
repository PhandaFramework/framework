<?php

namespace Phanda\Contracts\Database\Query;

use InvalidArgumentException;
use Phanda\Contracts\Bear\Query\ResultSet;
use Phanda\Contracts\Database\Connection\Connection;
use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Contracts\Database\Statement;
use Phanda\Database\Query\Expression\IdentifierExpression;
use Phanda\Database\Query\Expression\QueryExpression;
use Phanda\Database\ValueBinder;
use RuntimeException;

interface Query
{
    const TYPE_SELECT = 'select';
    const TYPE_INSERT = 'insert';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';

    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_LEFT = 'LEFT';
    const JOIN_TYPE_RIGHT = 'RIGHT';

    /**
     * Sets the internal connection instance of this query
     *
     * @param Connection $connection
     * @return $this
     */
    public function setConnection(Connection $connection);

    /**
     * Gets the connection instance of this query
     *
     * @return Connection
     */
    public function getConnection(): Connection;

    /**
     * Sets the internal ValueBinder for this query
     *
     * @param ValueBinder $binder
     * @return $this
     */
    public function setValueBinder(ValueBinder $binder);

    /**
     * Gets the internal ValueBinder for this query
     *
     * @return ValueBinder
     */
    public function getValueBinder(): ValueBinder;

    /**
     * Executes the current query and return the statement
     *
     * @return Statement
     */
    public function execute(): Statement;

    /**
     * Executes the query and gets the row count.
     *
     * @return int
     */
    public function getRowCountAndClose(): int;

    /**
     * Returns the current query to the domain-specific sql.
     *
     * @param ValueBinder|null $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder = null): string;

    /**
     * Iterate over each keyword on the query, calling back the visitor function
     *
     * @param callable $visitor
     * @param array $queryKeywords
     * @return $this
     */
    public function traverse(callable $visitor, array $queryKeywords = []);

    /**
     * Does a full-depth traversal of every item in the Query tree
     *
     * @param callable $callback
     * @return $this
     */
    public function traverseExpressions(callable $callback);

    /**
     * Adds fields to be returned by the execution of this query with a `SELECT` statement.
     *
     * Setting overwrite to true will reset currently selected fields.
     * Optionally, passing a named array of fields will perform a `SELECT AS` statement.
     *
     * @param array|string $fields
     * @param bool $overwrite
     * @return $this
     */
    public function select($fields = [], $overwrite = false);

    /**
     * Adds a distinct to fields in the current query
     *
     * @param array|string|bool $on
     * @param bool $overwrite
     * @return $this
     */
    public function distinct($on = [], $overwrite = false);

    /**
     * Adds a modifer to be performed after a `SELECT`
     *
     * @param $modifiers
     * @param bool $overwrite
     * @return $this
     */
    public function modifier($modifiers, $overwrite = false);

    /**
     * Adds a table(s) to the `FROM` clause of this statement
     *
     * @param string|array $tables
     * @param bool $overwrite
     * @return $this
     */
    public function from($tables, $overwrite = false);

    /**
     * Adds a single or multiple tables to be used as JOIN clauses to this query.
     *
     * @param array|string|null $tables
     * @param bool $overwrite
     * @return $this
     */
    public function join($tables, $overwrite = false);

    /**
     * Removes a join from this query by name/alias if it exists
     *
     * @param string $joinName
     * @return $this
     */
    public function removeJoin(string $joinName);

    /**
     * Adds a single left join to this query.
     *
     * This function is merely a helper function around join()
     * which handles the creation of the join array.
     *
     * @param string|array $table
     * @param string|array|ExpressionContract $conditions
     * @return $this
     */
    public function leftJoin($table, $conditions = []);

    /**
     * Adds a single inner join to this query.
     *
     * This function is merely a helper function around join()
     * which handles the creation of the join array.
     *
     * @param string|array $table
     * @param string|array|ExpressionContract $conditions
     * @return $this
     */
    public function innerJoin($table, $conditions = []);

    /**
     * Adds a single right join to this query.
     *
     * This function is merely a helper function around join()
     * which handles the creation of the join array.
     *
     * @param string|array $table
     * @param string|array|ExpressionContract $conditions
     * @return $this
     */
    public function rightJoin($table, $conditions = []);

    /**
     * Adds a condition or set of conditions to be used in the WHERE clause for this query.
     *
     * @param string|array|callable|ExpressionContract|null $conditions
     * @param bool $overwrite
     * @return $this
     */
    public function where($conditions = null, $overwrite = false);

    /**
     * Adds a condition to check where a field is not null
     *
     * @param array|string|ExpressionContract $fields
     * @return $this
     */
    public function whereNotNull($fields);

    /**
     * Adds a condition to check where a field is null
     *
     * @param array|string|ExpressionContract $fields
     * @return $this
     */
    public function whereNull($fields);

    /**
     * Adds a condition to check where a field is in a list
     *
     * @param string $field
     * @param array $values
     * @param array $options
     * @return $this
     */
    public function whereIn(string $field, array $values, array $options = []);

    /**
     * Adds a condition to check where a field is not in a list
     *
     * @param string $field
     * @param array $values
     * @param array $options
     * @return $this
     */
    public function whereNotIn(string $field, array $values, array $options = []);

    /**
     * Connects any previous where queries in this query, and conjugates it with an 'AND'
     *
     * @param string|array|callable|ExpressionContract $conditions
     * @return $this
     */
    public function andWhere($conditions);

    /**
     * Performs an order by on fields in this query
     *
     * Fields can be passed as a named array with their column as the key
     * and the direction they want to be ordered by as the value.
     *
     * @param array|string|callable|ExpressionContract $fields
     * @param bool $overwrite
     * @return $this
     */
    public function orderBy($fields, $overwrite = false);

    /**
     * Add an ORDER BY clause with an ASC direction.
     *
     * @param string|QueryExpression $field
     * @param bool $overwrite
     * @return $this
     */
    public function orderByAsc($field, $overwrite = false);

    /**
     * Add an ORDER BY clause with an DESC direction.
     *
     * @param string|QueryExpression $field
     * @param bool $overwrite
     * @return $this
     */
    public function orderByDesc($field, $overwrite = false);

    /**
     * Adds a GROUP BY clause to this query
     *
     * @param string|array|ExpressionContract $fields
     * @param bool $overwrite
     * @return $this
     */
    public function groupBy($fields, $overwrite = false);

    /**
     * Adds a 'HAVING' clause to this query
     *
     * @param string|array|callable|ExpressionContract|null $conditions
     * @param bool $overwrite
     * @return $this
     */
    public function having($conditions = null, $overwrite = false);

    /**
     * Adds a LIMIT to the query
     *
     * @param int|ExpressionContract $limit
     * @return $this
     */
    public function limit($limit);

    /**
     * Sets the number of records that should be skipped in the original result set
     *
     * @param int|ExpressionContract $offset
     * @return $this
     */
    public function offset($offset);

    /**
     * A helper function to handle the LIMIT and OFFSET calls for you.
     *
     * If limit is null, it will use the already existing limit applied
     * to this query. Meaning you can do $query->limit(10)->page(2)
     * Or alternatively, $query->page(2, 10);
     *
     * If limit is not set prior and no limit is passed, '25' is the default.
     * $query->page(1); Will return 25 results.
     *
     * @param int $page
     * @param int|ExpressionContract|null $limit
     * @return $this
     */
    public function page(int $page, $limit = null);

    /**
     * Adds a complete query to be used in conjunction with a 'UNION'
     *
     * @param string|Query $query
     * @param bool $overwrite
     * @return $this
     */
    public function union($query, $overwrite = false);

    /**
     * Adds a complete query to be used in conjunction with a 'UNION ALL'
     *
     * @param string|Query $query
     * @param bool $overwrite
     * @return $this
     */
    public function unionAll($query, $overwrite = false);

    /**
     * Creates an insert query
     *
     * @param array $columns
     * @return $this
     *
     * @throws RuntimeException When no columns are given
     */
    public function insert(array $columns);

    /**
     * Sets the table for the insert query to be inserted into
     *
     * For example $query->insert(...)->into('table')->values(...);
     * Or $query->into('table')->insert(...)->values(...);
     *
     * @param string $table
     * @return $this
     */
    public function into(string $table);

    /**
     * Sets the values for the insert of the query
     *
     * @param array|Query $data
     * @return $this
     */
    public function values($data);

    /**
     * Creates an update query
     *
     * @param string|ExpressionContract $table
     * @return $this
     *
     * @throws InvalidArgumentException When $table is not a string or ExpressionContract
     */
    public function update($table);

    /**
     * Sets one, or many fields of this update query
     *
     * Can be used like
     * $query->update('books')->set('title', 'The Great Gatsby');
     * $query->update('books')->set(['title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald']);
     *
     * @param string|array|callable|QueryExpression $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value = null);

    /**
     * Creates a delete query
     *
     * @param string|null $table
     * @return $this
     */
    public function delete(?string $table = null);

    /**
     * Appends an expression to the end of the generated query
     *
     * @param null $expression
     * @return $this
     */
    public function append($expression = null);

    /**
     * Creates an identifier from a string
     *
     * @param string $identifier
     * @return IdentifierExpression
     */
    public function identifier(string $identifier);

    /**
     * Gets a clause in the current query from the queryKeywords.
     *
     * @param $name
     * @return mixed
     */
    public function getClause($name);

    /**
     * Binds a value to a placeholder in the current query
     *
     * @param string|int $param
     * @param mixed $value
     * @return $this
     */
    public function bind($param, $value);

    /**
     * Add a result decorator to be used when returning the results
     *
     * @param callable|null $callback
     * @param bool $overwrite
     * @return $this
     */
    public function decorateResults(?callable $callback, $overwrite = false);

    /**
     * Gets the type of the current query
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Retrieve an external iterator
     *
     * @return Statement|ResultSet|null
     */
    public function getIterator();

	/**
	 * Enables/Disables buffered results.
	 *
	 * When enabled the results returned by this Query will be
	 * buffered. This enables you to iterate a result set multiple times, or
	 * both cache and iterate it.
	 *
	 * When disabled it will consume less memory as fetched results are not
	 * remembered for future iterations.
	 *
	 * @param bool $enable Whether or not to enable buffering
	 * @return $this
	 */
	public function enableBufferedResults(bool $enable = true);

	/**
	 * Disables buffered results.
	 *
	 * Disabling buffering will consume less memory as fetched results are not
	 * remembered for future iterations.
	 *
	 * @return $this
	 */
	public function disableBufferedResults();

	/**
	 * Returns whether buffered results are enabled/disabled.
	 *
	 * When enabled the results returned by this Query will be
	 * buffered. This enables you to iterate a result set multiple times, or
	 * both cache and iterate it.
	 *
	 * When disabled it will consume less memory as fetched results are not
	 * remembered for future iterations.
	 *
	 * @return bool
	 */
	public function isBufferedResultsEnabled(): bool;
}