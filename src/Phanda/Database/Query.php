<?php

namespace Phanda\Database;

use Phanda\Contracts\Database\Connection\Connection;
use Phanda\Contracts\Database\Statement;

class Query
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