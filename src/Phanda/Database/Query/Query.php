<?php

namespace Phanda\Database\Query;

use Phanda\Contracts\Database\Connection\Connection;
use Phanda\Contracts\Database\Statement;
use Phanda\Database\Query\Expression\OrderByExpression;
use Phanda\Database\Query\Expression\OrderClauseExpression;
use Phanda\Database\Query\Expression\QueryExpression;
use Phanda\Database\ValueBinder;
use Phanda\Support\PhandArr;
use Phanda\Contracts\Database\Query\Query as QueryContract;
use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;

class Query implements QueryContract
{
    const TYPE_SELECT = 'select';

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
        'epilog' => null
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
     * @return $this
     */
    public function setConnection(Connection $connection): Query
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
     * @return Query
     */
    public function setValueBinder(ValueBinder $binder): Query
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
     * @return Query
     */
    public function traverse(callable $visitor, array $queryKeywords = []): Query
    {
        $queryKeywords = $queryKeywords ?: array_keys($this->queryKeywords);
        foreach ($queryKeywords as $keyword) {
            $visitor($this->queryKeywords[$keyword], $keyword);
        }

        return $this;
    }

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
    public function select($fields = [], $overwrite = false): Query
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
     * @return Query
     */
    public function distinct($on = [], $overwrite = false): Query
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
     * @return Query
     */
    public function modifier($modifiers, $overwrite = false): Query
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
     * @return Query
     */
    public function from($tables, $overwrite = false): Query
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
     * @return Query
     */
    public function join($tables, $overwrite = false): Query
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
     * @return $this
     */
    public function removeJoin(string $joinName): Query
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
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * @return Query
     */
    public function where($conditions = null, $overwrite = false): Query
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
     * @return Query
     */
    public function whereNotNull($fields): Query
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
     * @return Query
     */
    public function whereNull($fields): Query
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
     * @return Query
     */
    public function whereIn(string $field, array $values, array $options = []): Query
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
     * @return Query
     */
    public function whereNotIn(string $field, array $values, array $options = []): Query
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
     * @return Query
     */
    public function andWhere($conditions): Query
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
     * @return Query
     */
    public function orderBy($fields, $overwrite = false): Query
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
     * @return Query
     */
    public function orderByAsc($field, $overwrite = false): Query
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
     * @return Query
     */
    public function orderByDesc($field, $overwrite = false): Query
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
     * @return Query
     */
    public function group($fields, $overwrite = false): Query
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
     * @return $this
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
     * @param Statement $statement
     * @return Statement
     */
    protected function decorateStatement(Statement $statement): Statement
    {
        $driver = $this->getConnection()->getDriver();

        // TODO: Decorate statement here with callback functions using the
        //       CallbackStatement decorator.

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