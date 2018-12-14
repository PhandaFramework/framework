<?php

namespace Phanda\Database;

use Phanda\Contracts\Database\Connection\Connection;
use Phanda\Contracts\Database\Statement;
use Phanda\Support\PhandArr;

class Query
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
        if(!is_string($fields) && is_callable($fields)) {
            $fields = $fields($this);
        }

        $fields = PhandArr::makeArray($fields);

        if($overwrite) {
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

        if($overwrite) {
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

        if($overwrite) {
            $this->queryKeywords['from'] = $tables;
        } else {
            $this->queryKeywords['from'] = array_merge($this->queryKeywords['from'], $tables);
        }

        $this->makeDirty();
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

}