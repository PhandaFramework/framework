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

    public function execute(): Statement
    {
        $this->connection->executeQuery($this);

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

    protected function decorateStatement()
    {

    }

}