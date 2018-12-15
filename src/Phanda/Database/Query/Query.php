<?php

namespace Phanda\Database\Query;

use InvalidArgumentException;
use Phanda\Contracts\Database\Connection\Connection;
use Phanda\Contracts\Database\Statement;
use Phanda\Database\Query\Expression\IdentifierExpression;
use Phanda\Database\Query\Expression\OrderByExpression;
use Phanda\Database\Query\Expression\OrderClauseExpression;
use Phanda\Database\Query\Expression\QueryExpression;
use Phanda\Database\Query\Expression\ValuesExpression;
use Phanda\Database\Statement\CallbackStatement;
use Phanda\Database\ValueBinder;
use Phanda\Support\PhandArr;
use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use RuntimeException;
use Phanda\Contracts\Database\Query\Query as QueryContract;

class Query implements \IteratorAggregate, QueryContract
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $type = 'select';

    /**
     * @var array
     */
    protected $queryKeywords = [
        'delete' => true,
        'update' => [],
        'set' => [],
        'insert' => [],
        'values' => [],
        'select' => [],
        'distinct' => false,
        'modifier' => [],
        'from' => [],
        'join' => [],
        'where' => null,
        'group' => [],
        'having' => null,
        'order' => null,
        'limit' => null,
        'offset' => null,
        'union' => [],
        'append' => null
    ];

    /**
     * @var bool
     */
    protected $dirty = false;

    /**
     * @var Statement|null
     */
    protected $resultStatement;

    /**
     * @var ValueBinder
     */
    protected $valueBinder;

    /**
     * Callback functions used to decorate results
     *
     * @var array
     */
    protected $resultDecorators = [];

    /**
     * Query constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Sets the internal connection instance of this query
     *
     * @param Connection $connection
     * @return QueryContract
     */
    public function setConnection(Connection $connection): QueryContract
    {
        $this->makeDirty();
        $this->connection = $connection;
        return $this;
    }

    /**
     * Gets the connection instance of this query
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Sets the internal ValueBinder for this query
     *
     * @param ValueBinder $binder
     * @return QueryContract
     */
    public function setValueBinder(ValueBinder $binder): QueryContract
    {
        $this->valueBinder = $binder;
        return $this;
    }

    /**
     * Gets the internal ValueBinder for this query
     *
     * @return ValueBinder
     */
    public function getValueBinder(): ValueBinder
    {
        if (is_null($this->valueBinder)) {
            $this->valueBinder = new ValueBinder();
        }

        return $this->valueBinder;
    }

    /**
     * Executes the current query and return the statement
     *
     * @return Statement
     */
    public function execute(): Statement
    {
        $statement = $this->connection->executeQuery($this);
        $this->resultStatement = $this->decorateStatement($statement);
        $this->dirty = false;

        return $this->resultStatement;
    }

    /**
     * Executes the query and gets the row count.
     *
     * @return int
     */
    public function getRowCountAndClose(): int
    {
        $statement = $this->execute();
        try {
            return $statement->getRowCount();
        } finally {
            $statement->closeCursor();
        }
    }

    /**
     * Returns the current query to the domain-specific sql.
     *
     * @param ValueBinder|null $generator
     * @return string
     */
    public function toSql(ValueBinder $generator = null): string
    {
        if (!$generator) {
            $generator = $this->getValueBinder();
            $generator->reset();
        }

        return $this->getConnection()->compileQuery($this, $generator);
    }

    /**
     * Iterate over each keyword on the query, calling back the visitor function
     *
     * @param callable $visitor
     * @param array $queryKeywords
     * @return QueryContract
     */
    public function traverse(callable $visitor, array $queryKeywords = []): QueryContract
    {
        $queryKeywords = $queryKeywords ?: array_keys($this->queryKeywords);
        foreach ($queryKeywords as $keyword) {
            $visitor($this->queryKeywords[$keyword], $keyword);
        }

        return $this;
    }

    /**
     * Does a full-depth traversal of every item in the Query tree
     *
     * @param callable $callback
     * @return QueryContract
     */
    public function traverseExpressions(callable $callback): QueryContract
    {
        $visitor = function ($expression) use (&$visitor, $callback) {
            if (is_array($expression)) {
                foreach ($expression as $e) {
                    $visitor($e);
                }

                return null;
            }

            if ($expression instanceof ExpressionContract) {
                $expression->traverse($visitor);

                if (!($expression instanceof self)) {
                    $callback($expression);
                }
            }
        };

        return $this->traverse($visitor);
    }

    /**
     * Adds fields to be returned by the execution of this query with a `SELECT` statement.
     *
     * Setting overwrite to true will reset currently selected fields.
     * Optionally, passing a named array of fields will perform a `SELECT AS` statement.
     *
     * @param array|string $fields
     * @param bool $overwrite
     * @return QueryContract
     */
    public function select($fields = [], $overwrite = false): QueryContract
    {
        if (!is_string($fields) && is_callable($fields)) {
            $fields = $fields($this);
        }

        $fields = PhandArr::makeArray($fields);

        if ($overwrite) {
            $this->queryKeywords['select'] = $fields;
        } else {
            $this->queryKeywords['select'] = array_merge($this->queryKeywords['select'], $fields);
        }

        $this->makeDirty();
        $this->type = self::TYPE_SELECT;

        return $this;
    }

    /**
     * Adds a distinct to fields in the current query
     *
     * @param array|string|bool $on
     * @param bool $overwrite
     * @return QueryContract
     */
    public function distinct($on = [], $overwrite = false): QueryContract
    {
        if ($on === []) {
            $on = true;
        } else {
            $on = PhandArr::makeArray($on);

            $merge = [];
            if (is_array($this->queryKeywords['distinct'])) {
                $merge = $this->queryKeywords['distinct'];
            }
            $on = $overwrite ? array_values($on) : array_merge($merge, array_values($on));
        }

        $this->queryKeywords['distinct'] = $on;
        $this->makeDirty();

        return $this;
    }

    /**
     * Adds a modifer to be performed after a `SELECT`
     *
     * @param $modifiers
     * @param bool $overwrite
     * @return QueryContract
     */
    public function modifier($modifiers, $overwrite = false): QueryContract
    {
        $modifiers = PhandArr::makeArray($modifiers);

        if ($overwrite) {
            $this->queryKeywords['modifier'] = $modifiers;
        } else {
            $this->queryKeywords['modifier'] = array_merge($this->queryKeywords['modifier'], $modifiers);
        }

        return $this;
    }

    /**
     * Adds a table(s) to the `FROM` clause of this statement
     *
     * @param string|array $tables
     * @param bool $overwrite
     * @return QueryContract
     */
    public function from($tables, $overwrite = false): QueryContract
    {
        $tables = PhandArr::makeArray($tables);

        if ($overwrite) {
            $this->queryKeywords['from'] = $tables;
        } else {
            $this->queryKeywords['from'] = array_merge($this->queryKeywords['from'], $tables);
        }

        $this->makeDirty();
        return $this;
    }

    /**
     * Adds a single or multiple tables to be used as JOIN clauses to this query.
     *
     * @param array|string|null $tables
     * @param bool $overwrite
     * @return QueryContract
     */
    public function join($tables, $overwrite = false): QueryContract
    {
        $tables = PhandArr::makeArray($tables);

        $joins = [];
        $i = count($this->queryKeywords['join']);
        foreach ($tables as $alias => $t) {
            if (!is_array($t)) {
                $t = ['table' => $t, 'conditions' => $this->newExpression()];
            }

            if (!is_string($t['conditions']) && is_callable($t['conditions'])) {
                $t['conditions'] = $t['conditions']($this->newExpression(), $this);
            }

            if (!($t['conditions'] instanceof ExpressionContract)) {
                $t['conditions'] = $this->newExpression()->addConditions($t['conditions']);
            }

            $alias = is_string($alias) ? $alias : null;
            $joins[$alias ?: $i++] = $t + ['type' => self::JOIN_TYPE_INNER, 'alias' => $alias];
        }

        if ($overwrite) {
            $this->queryKeywords['join'] = $joins;
        } else {
            $this->queryKeywords['join'] = array_merge($this->queryKeywords['join'], $joins);
        }

        $this->makeDirty();
        return $this;
    }

    /**
     * Removes a join from this query by name/alias if it exists
     *
     * @param string $joinName
     * @return QueryContract
     */
    public function removeJoin(string $joinName): QueryContract
    {
        unset($this->queryKeywords['join'][$joinName]);
        $this->makeDirty();

        return $this;
    }

    /**
     * Adds a single left join to this query.
     *
     * This function is merely a helper function around join()
     * which handles the creation of the join array.
     *
     * @param string|array $table
     * @param string|array|ExpressionContract $conditions
     * @return QueryContract
     */
    public function leftJoin($table, $conditions = [])
    {
        return $this->join(
            $this->makeJoin(
                $table,
                $conditions,
                self::JOIN_TYPE_LEFT
            )
        );
    }

    /**
     * Adds a single inner join to this query.
     *
     * This function is merely a helper function around join()
     * which handles the creation of the join array.
     *
     * @param string|array $table
     * @param string|array|ExpressionContract $conditions
     * @return QueryContract
     */
    public function innerJoin($table, $conditions = [])
    {
        return $this->join(
            $this->makeJoin(
                $table,
                $conditions,
                self::JOIN_TYPE_INNER
            )
        );
    }

    /**
     * Adds a single right join to this query.
     *
     * This function is merely a helper function around join()
     * which handles the creation of the join array.
     *
     * @param string|array $table
     * @param string|array|ExpressionContract $conditions
     * @return QueryContract
     */
    public function rightJoin($table, $conditions = [])
    {
        return $this->join(
            $this->makeJoin(
                $table,
                $conditions,
                self::JOIN_TYPE_RIGHT
            )
        );
    }

    /**
     * Adds a condition or set of conditions to be used in the WHERE clause for this query.
     *
     * @param string|array|callable|ExpressionContract|null $conditions
     * @param bool $overwrite
     * @return QueryContract
     */
    public function where($conditions = null, $overwrite = false): QueryContract
    {
        if ($overwrite) {
            $this->queryKeywords['where'] = $this->newExpression();
        }

        $this->conjugateQuery('where', $conditions, 'AND');

        return $this;
    }

    /**
     * Adds a condition to check where a field is not null
     *
     * @param array|string|ExpressionContract $fields
     * @return QueryContract
     */
    public function whereNotNull($fields): QueryContract
    {
        $fields = PhandArr::makeArray($fields);
        $expression = $this->newExpression();

        foreach ($fields as $field) {
            $expression->isNotNull($field);
        }

        return $this->where($expression);
    }

    /**
     * Adds a condition to check where a field is null
     *
     * @param array|string|ExpressionContract $fields
     * @return QueryContract
     */
    public function whereNull($fields): QueryContract
    {
        $fields = PhandArr::makeArray($fields);
        $expression = $this->newExpression();

        foreach ($fields as $field) {
            $expression->isNull($field);
        }

        return $this->where($expression);
    }

    /**
     * Adds a condition to check where a field is in a list
     *
     * @param string $field
     * @param array $values
     * @param array $options
     * @return QueryContract
     */
    public function whereIn(string $field, array $values, array $options = []): QueryContract
    {
        $options += [
            'allowEmpty' => false,
        ];

        if ($options['allowEmpty'] && !$values) {
            return $this->where('1=0');
        }

        return $this->where([$field . ' IN' => $values]);
    }

    /**
     * Adds a condition to check where a field is not in a list
     *
     * @param string $field
     * @param array $values
     * @param array $options
     * @return QueryContract
     */
    public function whereNotIn(string $field, array $values, array $options = []): QueryContract
    {
        $options += [
            'allowEmpty' => false,
        ];

        if ($options['allowEmpty'] && !$values) {
            return $this->where('1=0');
        }

        return $this->where([$field . ' NOT IN' => $values]);
    }

    /**
     * Connects any previous where queries in this query, and conjugates it with an 'AND'
     *
     * @param string|array|callable|ExpressionContract $conditions
     * @return QueryContract
     */
    public function andWhere($conditions): QueryContract
    {
        $this->conjugateQuery('where', $conditions, 'AND');
        return $this;
    }

    /**
     * Performs an order by on fields in this query
     *
     * Fields can be passed as a named array with their column as the key
     * and the direction they want to be ordered by as the value.
     *
     * @param array|string|callable|ExpressionContract $fields
     * @param bool $overwrite
     * @return QueryContract
     */
    public function orderBy($fields, $overwrite = false): QueryContract
    {
        if ($overwrite) {
            $this->queryKeywords['order'] = null;
        }

        if (!$fields) {
            return $this;
        }

        if (!$this->queryKeywords['order']) {
            $this->queryKeywords['order'] = new OrderByExpression();
        }
        $this->conjugateQuery('order', $fields, '');

        return $this;
    }

    /**
     * Add an ORDER BY clause with an ASC direction.
     *
     * @param string|QueryExpression $field
     * @param bool $overwrite
     * @return QueryContract
     */
    public function orderByAsc($field, $overwrite = false): QueryContract
    {
        if ($overwrite) {
            $this->queryKeywords['order'] = null;
        }

        if (!$field) {
            return $this;
        }

        if (!$this->queryKeywords['order']) {
            $this->queryKeywords['order'] = new OrderByExpression();
        }

        $this->queryKeywords['order']->addConditions(new OrderClauseExpression($field, 'ASC'));

        return $this;
    }

    /**
     * Add an ORDER BY clause with an DESC direction.
     *
     * @param string|QueryExpression $field
     * @param bool $overwrite
     * @return QueryContract
     */
    public function orderByDesc($field, $overwrite = false): QueryContract
    {
        if ($overwrite) {
            $this->queryKeywords['order'] = null;
        }

        if (!$field) {
            return $this;
        }

        if (!$this->queryKeywords['order']) {
            $this->queryKeywords['order'] = new OrderByExpression();
        }

        $this->queryKeywords['order']->addConditions(new OrderClauseExpression($field, 'DESC'));

        return $this;
    }

    /**
     * Adds a GROUP BY clause to this query
     *
     * @param string|array|ExpressionContract $fields
     * @param bool $overwrite
     * @return QueryContract
     */
    public function groupBy($fields, $overwrite = false): QueryContract
    {
        if ($overwrite) {
            $this->queryKeywords['group'] = [];
        }

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        $this->queryKeywords['group'] = array_merge($this->queryKeywords['group'], array_values($fields));
        $this->makeDirty();

        return $this;
    }

    /**
     * Adds a 'HAVING' clause to this query
     *
     * @param string|array|callable|ExpressionContract|null $conditions
     * @param bool $overwrite
     * @return QueryContract
     */
    public function having($conditions = null, $overwrite = false)
    {
        if ($overwrite) {
            $this->queryKeywords['having'] = $this->newExpression();
        }
        $this->conjugateQuery('having', $conditions, 'AND');

        return $this;
    }

    /**
     * Adds a LIMIT to the query
     *
     * @param int|ExpressionContract $limit
     * @return QueryContract
     */
    public function limit($limit): QueryContract
    {
        $this->makeDirty();

        if ($limit !== null && !is_object($limit)) {
            $limit = (int)$limit;
        }

        $this->queryKeywords['limit'] = $limit;
        return $this;
    }

    /**
     * Sets the number of records that should be skipped in the original result set
     *
     * @param int|ExpressionContract $offset
     * @return QueryContract
     */
    public function offset($offset): QueryContract
    {
        $this->makeDirty();

        if ($offset !== null && !is_object($offset)) {
            $offset = (int)$offset;
        }

        $this->queryKeywords['offset'] = $offset;
        return $this;
    }

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
     * @return QueryContract
     */
    public function page(int $page, $limit = null)
    {
        if ($page < 1) {
            throw new InvalidArgumentException('Page must be 1 or greater.');
        }

        if ($limit !== null) {
            $this->limit($limit);
        }

        $limit = $this->getClause('limit');

        if ($limit === null) {
            $limit = 25;
            $this->limit($limit);
        }

        $offset = ($page - 1) * $limit;

        if (PHP_INT_MAX <= $offset) {
            $offset = PHP_INT_MAX;
        }

        $this->offset((int)$offset);

        return $this;
    }

    /**
     * Adds a complete query to be used in conjunction with a 'UNION'
     *
     * @param string|QueryContract $query
     * @param bool $overwrite
     * @return QueryContract
     */
    public function union($query, $overwrite = false): QueryContract
    {
        if ($overwrite) {
            $this->queryKeywords['union'] = [];
        }

        $this->queryKeywords['union'][] = [
            'all' => false,
            'query' => $query
        ];

        $this->makeDirty();

        return $this;
    }

    /**
     * Adds a complete query to be used in conjunction with a 'UNION ALL'
     *
     * @param string|QueryContract $query
     * @param bool $overwrite
     * @return QueryContract
     */
    public function unionAll($query, $overwrite = false)
    {
        if ($overwrite) {
            $this->queryKeywords['union'] = [];
        }

        $this->queryKeywords['union'][] = [
            'all' => true,
            'query' => $query
        ];

        $this->makeDirty();

        return $this;
    }

    /**
     * Creates an insert query
     *
     * @param array $columns
     * @return QueryContract
     *
     * @throws RuntimeException When no columns are given
     */
    public function insert(array $columns): QueryContract
    {
        if (empty($columns)) {
            throw new RuntimeException('At least 1 column is required to perform an insert query.');
        }

        $this->makeDirty();
        $this->type = self::TYPE_INSERT;
        $this->queryKeywords['insert'][1] = $columns;

        if (!$this->queryKeywords['values']) {
            $this->queryKeywords['values'] = new ValuesExpression($columns);
        } else {
            $this->queryKeywords['values']->setColumns($columns);
        }

        return $this;
    }

    /**
     * Sets the table for the insert query to be inserted into
     *
     * For example $query->insert(...)->into('table')->values(...);
     * Or $query->into('table')->insert(...)->values(...);
     *
     * @param string $table
     * @return QueryContract
     */
    public function into(string $table): QueryContract
    {
        $this->makeDirty();
        $this->type = self::TYPE_INSERT;
        $this->queryKeywords['insert'][0] = $table;

        return $this;
    }

    /**
     * Sets the values for the insert of the query
     *
     * @param array|QueryContract $data
     * @return QueryContract
     */
    public function values($data)
    {
        if ($this->type !== self::TYPE_INSERT || empty($this->queryKeywords['insert'])) {
            throw new RuntimeException('You cannot add values to the query builder before defining the columns.');
        }

        $this->makeDirty();

        if ($data instanceof ValuesExpression) {
            $this->queryKeywords['values'] = $data;
            return $this;
        }

        $this->queryKeywords['values']->add($data);
        return $this;
    }

    /**
     * Creates an update query
     *
     * @param string|ExpressionContract $table
     * @return QueryContract
     *
     * @throws InvalidArgumentException When $table is not a string or ExpressionContract
     */
    public function update($table): QueryContract
    {
        if (!is_string($table) && !($table instanceof ExpressionContract)) {
            $text = 'Table must be of type string or "%s", got "%s"';
            $message = sprintf($text, ExpressionContract::class, gettype($table));

            throw new InvalidArgumentException($message);
        }

        $this->makeDirty();
        $this->type = self::TYPE_UPDATE;
        $this->queryKeywords['update'][0] = $table;

        return $this;
    }

    /**
     * Sets one, or many fields of this update query
     *
     * Can be used like
     * $query->update('books')->set('title', 'The Great Gatsby');
     * $query->update('books')->set(['title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald']);
     *
     * @param string|array|callable|QueryExpression $key
     * @param mixed $value
     * @return QueryContract
     */
    public function set($key, $value = null): QueryContract
    {
        if (empty($this->queryKeywords['set'])) {
            $this->queryKeywords['set'] = $this->newExpression()->setConjunction(',');
        }

        if ($this->queryKeywords['set']->isCallable($key)) {
            $exp = $this->newExpression()->setConjunction(',');
            $this->queryKeywords['set']->addConditions($key($exp));
            return $this;
        }

        if (is_array($key) || $key instanceof ExpressionContract) {
            $this->queryKeywords['set']->addConditions($key);
            return $this;
        }

        $this->queryKeywords['set']->equal($key, $value);

        return $this;
    }

    /**
     * Creates a delete query
     *
     * @param string|null $table
     * @return QueryContract
     */
    public function delete(?string $table = null): QueryContract
    {
        $this->makeDirty();
        $this->type = self::TYPE_DELETE;

        if ($table !== null) {
            $this->from($table);
        }

        return $this;
    }

    /**
     * Appends an expression to the end of the generated query
     *
     * @param null $expression
     * @return QueryContract
     */
    public function append($expression = null): QueryContract
    {
        $this->makeDirty();
        $this->queryKeywords['append'] = $expression;
        return $this;
    }

    /**
     * Creates an identifier from a string
     *
     * @param string $identifier
     * @return IdentifierExpression
     */
    public function identifier(string $identifier)
    {
        return new IdentifierExpression($identifier);
    }

    /**
     * Gets a clause in the current query from the queryKeywords.
     *
     * @param $name
     * @return mixed
     */
    public function getClause($name)
    {
        if (!array_key_exists($name, $this->queryKeywords)) {
            $clauses = implode(', ', array_keys($this->queryKeywords));
            throw new InvalidArgumentException("The '{$name}' clause has not been defined. Valid clauses are: {$clauses}");
        }

        return $this->queryKeywords[$name];
    }

    /**
     * Binds a value to a placeholder in the current query
     *
     * @param string|int $param
     * @param mixed $value
     * @return QueryContract
     */
    public function bind($param, $value): QueryContract
    {
        $this->getValueBinder()->bind($param, $value);
        return $this;
    }

    /**
     * Add a result decorator to be used when returning the results
     *
     * @param callable|null $callback
     * @param bool $overwrite
     * @return QueryContract
     */
    public function decorateResults(?callable $callback, $overwrite = false): QueryContract
    {
        if ($overwrite) {
            $this->resultDecorators = [];
        }

        if ($callback !== null) {
            $this->resultDecorators[] = $callback;
        }

        return $this;
    }

    /**
     * Gets the type of the current query
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Retrieve an external iterator
     *
     * @return Statement|null
     */
    public function getIterator(): ?Statement
    {
        if ($this->resultStatement === null || $this->dirty) {
            $this->resultStatement = $this->execute();
        }

        return $this->resultStatement;
    }

    /**
     * Gets the SQL of the current query
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toSql();
    }

    /**
     * Marks a query as dirty, and resets any value bindings if need be.
     */
    protected function makeDirty()
    {
        $this->dirty = true;

        if ($this->resultStatement && $this->valueBinder) {
            $this->valueBinder->reset();
        }
    }

    /**
     * Decorate statements with the registered result decorators
     *
     * @param Statement $statement
     * @return Statement
     */
    protected function decorateStatement(Statement $statement): Statement
    {
        $driver = $this->getConnection()->getDriver();

        foreach ($this->resultDecorators as $decorator) {
            $statement = new CallbackStatement($statement, $driver, $decorator);
        }

        return $statement;
    }

    /**
     * Creates a new QueryExpression for this query
     *
     * @param mixed $rawExpression
     * @return QueryExpression
     */
    protected function newExpression($rawExpression = null): QueryExpression
    {
        $expression = new QueryExpression();

        if ($rawExpression !== null) {
            $expression->addConditions($rawExpression);
        }

        return $expression;
    }

    /**
     * Prepares an array to be passed as the parameter for creating a join
     *
     * @param string|array $table
     * @param string|array|ExpressionContract $conditions
     * @param string $joinType
     * @return array
     */
    protected function makeJoin($table, $conditions, $joinType): array
    {
        $alias = $table;

        if (is_array($table)) {
            $alias = key($table);
            $table = current($table);
        }

        return [
            $alias => [
                'table' => $table,
                'conditions' => $conditions,
                'type' => $joinType
            ]
        ];
    }

    /**
     * Conjugates a part of the query with an expression
     *
     * @param string $part
     * @param string|callable|array|ExpressionContract|null $append
     * @param string $conjunction
     */
    protected function conjugateQuery(string $part, $append, string $conjunction)
    {
        $expression = $this->queryKeywords[$part] ?: $this->newExpression();
        if (empty($append)) {
            $this->queryKeywords[$part] = $expression;

            return;
        }

        if ($expression->isCallable($append)) {
            $append = $append($this->newExpression(), $this);
        }

        if ($expression->getConjunction() === $conjunction) {
            $expression->addConditions($append);
        } else {
            $expression = $this->newExpression()
                ->setConjunction($conjunction)
                ->addConditions([$expression, $append]);
        }

        $this->queryKeywords[$part] = $expression;
        $this->makeDirty();
    }
}