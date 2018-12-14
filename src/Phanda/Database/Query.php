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
     * Query constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

}