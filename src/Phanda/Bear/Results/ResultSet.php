<?php

namespace Phanda\Bear\Results;

use Phanda\Bear\Query\Builder;
use Phanda\Contracts\Database\Statement;
use Phanda\Dictionary\Dictionary;
use Phanda\Contracts\Bear\Query\ResultSet as ResultSetContract;
use SplFixedArray;

/**
 * Class ResultSet
 * @package Phanda\Bear\Results
 *
 * Fill this class out more to add additional functionality to the
 * dictionary that gets returned from the BearORM calls.
 *
 * @todo Make this an iterator and not perform a fetch all
 *       in the constructor. This will increase performance.
 */
class ResultSet extends Dictionary implements ResultSetContract
{
    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var Statement
     */
    protected $statement;

    /**
     * @var int
     */
    protected $count;

    /**
     * ResultSet constructor.
     * @param Builder $query
     * @param Statement $statement
     */
    public function __construct(Builder $query, Statement $statement)
    {
        $this->query = $query;
        $this->statement = $statement;

        $items = $statement->fetchAll(Statement::FETCH_TYPE_ASSOC);
        parent::__construct($items);
    }
}