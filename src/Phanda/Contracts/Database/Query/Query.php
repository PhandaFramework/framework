<?php

namespace Phanda\Contracts\Database\Query;

use InvalidArgumentException;
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
     * @return Query
     */
    public function setConnection(Connection $connection): Query;

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
     * @return Query
     */
    public function setValueBinder(ValueBinder $binder): Query;

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
     * @param ValueBinder|null $generator
     * @return string
     */
    public function toSql(ValueBinder $generator = null): string;

    /**
     * Iterate over each keyword on the query, calling back the visitor function
     *
     * @param callable $visitor
     * @param array $queryKeywords
     * @return Query
     */
    public function traverse(callable $visitor, array $queryKeywords = []): Query;

    /**
     * Does a full-depth traversal of every item in the Query tree
     *
     * @param callable $callback
     * @return Query
     */
    public function traverseExpressions(callable $callback): Query;

    /**
     * Adds fields to be returned by the execution of this query with a `SELECT` statement.
     *
     * Setting overwrite to true will reset currently selected fields.
     * Optionally, passing a named array of fields will perform a `SELECT AS` statement.
     *
     * @param array|string $fields
     * @param bool $overwrite
     * @return Query
     */
    public function select($fields = [], $overwrite = false): Query;

    /**
     * Adds a distinct to fields in the current query
     *
     * @param array|string|bool $on
     * @param bool $overwrite
     * @return Query
     */
    public function distinct($on = [], $overwrite = false): Query;

    /**
     * Adds a modifer to be performed after a `SELECT`
     *
     * @param $modifiers
     * @param bool $overwrite
     * @return Query
     */
    public function modifier($modifiers, $overwrite = false): Query;

    /**
     * Adds a table(s) to the `FROM` clause of this statement
     *
     * @param string|array $tables
     * @param bool $overwrite
     * @return Query
     */
    public function from($tables, $overwrite = false): Query;

    /**
     * Adds a single or multiple tables to be used as JOIN clauses to this query.
     *
     * @param array|string|null $tables
     * @param bool $overwrite
     * @return Query
     */
    public function join($tables, $overwrite = false): Query;

    /**
     * Removes a join from this query by name/alias if it exists
     *
     * @param string $joinName
     * @return Query
     */
    public function removeJoin(string $joinName): Query;

    /**
     * Adds a single left join to this query.
     *
     * This function is merely a helper function around join()
     * which handles the creation of the join array.
     *
     * @param string|array $table
     * @param string|array|ExpressionContract $conditions
     * @return Query
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
     * @return Query
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
     * @return Query
     */
    public function rightJoin($table, $conditions = []);

    /**
     * Adds a condition or set of conditions to be used in the WHERE clause for this query.
     *
     * @param string|array|callable|ExpressionContract|null $conditions
     * @param bool $overwrite
     * @return Query
     */
    public function where($conditions = null, $overwrite = false): Query;

    /**
     * Adds a condition to check where a field is not null
     *
     * @param array|string|ExpressionContract $fields
     * @return Query
     */
    public function whereNotNull($fields): Query;

    /**
     * Adds a condition to check where a field is null
     *
     * @param array|string|ExpressionContract $fields
     * @return Query
     */
    public function whereNull($fields): Query;

    /**
     * Adds a condition to check where a field is in a list
     *
     * @param string $field
     * @param array $values
     * @param array $options
     * @return Query
     */
    public function whereIn(string $field, array $values, array $options = []): Query;

    /**
     * Adds a condition to check where a field is not in a list
     *
     * @param string $field
     * @param array $values
     * @param array $options
     * @return Query
     */
    public function whereNotIn(string $field, array $values, array $options = []): Query;

    /**
     * Connects any previous where queries in this query, and conjugates it with an 'AND'
     *
     * @param string|array|callable|ExpressionContract $conditions
     * @return Query
     */
    public function andWhere($conditions): Query;

    /**
     * Performs an order by on fields in this query
     *
     * Fields can be passed as a named array with their column as the key
     * and the direction they want to be ordered by as the value.
     *
     * @param array|string|callable|ExpressionContract $fields
     * @param bool $overwrite
     * @return Query
     */
    public function orderBy($fields, $overwrite = false): Query;

    /**
     * Add an ORDER BY clause with an ASC direction.
     *
     * @param string|QueryExpression $field
     * @param bool $overwrite
     * @return Query
     */
    public function orderByAsc($field, $overwrite = false): Query;

    /**
     * Add an ORDER BY clause with an DESC direction.
     *
     * @param string|QueryExpression $field
     * @param bool $overwrite
     * @return Query
     */
    public function orderByDesc($field, $overwrite = false): Query;

    /**
     * Adds a GROUP BY clause to this query
     *
     * @param string|array|ExpressionContract $fields
     * @param bool $overwrite
     * @return Query
     */
    public function groupBy($fields, $overwrite = false): Query;

    /**
     * Adds a 'HAVING' clause to this query
     *
     * @param string|array|callable|ExpressionContract|null $conditions
     * @param bool $overwrite
     * @return Query
     */
    public function having($conditions = null, $overwrite = false);

    /**
     * Adds a LIMIT to the query
     *
     * @param int|ExpressionContract $limit
     * @return Query
     */
    public function limit($limit): Query;

    /**
     * Sets the number of records that should be skipped in the original result set
     *
     * @param int|ExpressionContract $offset
     * @return Query
     */
    public function offset($offset): Query;

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
     * @return Query
     */
    public function page(int $page, $limit = null);

    /**
     * Adds a complete query to be used in conjunction with a 'UNION'
     *
     * @param string|Query $query
     * @param bool $overwrite
     * @return Query
     */
    public function union($query, $overwrite = false): Query;

    /**
     * Adds a complete query to be used in conjunction with a 'UNION ALL'
     *
     * @param string|Query $query
     * @param bool $overwrite
     * @return Query
     */
    public function unionAll($query, $overwrite = false);

    /**
     * Creates an insert query
     *
     * @param array $columns
     * @return Query
     *
     * @throws RuntimeException When no columns are given
     */
    public function insert(array $columns): Query;

    /**
     * Sets the table for the insert query to be inserted into
     *
     * For example $query->insert(...)->into('table')->values(...);
     * Or $query->into('table')->insert(...)->values(...);
     *
     * @param string $table
     * @return Query
     */
    public function into(string $table): Query;

    /**
     * Sets the values for the insert of the query
     *
     * @param array|Query $data
     * @return Query
     */
    public function values($data);

    /**
     * Creates an update query
     *
     * @param string|ExpressionContract $table
     * @return Query
     *
     * @throws InvalidArgumentException When $table is not a string or ExpressionContract
     */
    public function update($table): Query;

    /**
     * Sets one, or many fields of this update query
     *
     * Can be used like
     * $query->update('books')->set('title', 'The Great Gatsby');
     * $query->update('books')->set(['title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald']);
     *
     * @param string|array|callable|QueryExpression $key
     * @param mixed $value
     * @return Query
     */
    public function set($key, $value = null): Query;

    /**
     * Creates a delete query
     *
     * @param string|null $table
     * @return Query
     */
    public function delete(?string $table = null): Query;

    /**
     * Appends an expression to the end of the generated query
     *
     * @param null $expression
     * @return Query
     */
    public function append($expression = null): Query;

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
     * @return Query
     */
    public function bind($param, $value): Query;

    /**
     * Add a result decorator to be used when returning the results
     *
     * @param callable|null $callback
     * @param bool $overwrite
     * @return Query
     */
    public function decorateResults(?callable $callback, $overwrite = false): Query;

    /**
     * Gets the type of the current query
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Retrieve an external iterator
     *
     * @return Statement|null
     */
    public function getIterator(): ?Statement;
}