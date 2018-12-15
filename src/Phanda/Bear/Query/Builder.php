<?php

namespace Phanda\Bear\Query;

use Phanda\Contracts\Bear\Table\TableRepository;
use Phanda\Contracts\Database\Statement;
use Phanda\Database\Query\Query as DatabaseQueryBuilder;
use Phanda\Contracts\Bear\Query\Builder as QueryBuilderContract;

class Builder extends DatabaseQueryBuilder implements QueryBuilderContract, \JsonSerializable
{

    /**
     * @var TableRepository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $mapReduce = [];

    /**
     * @var callable[]
     */
    protected $queryFormatters = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var bool
     */
    protected $eagerLoaded = false;

    /**
     * Specify data which should be serialized to JSON
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }

    /**
     * Sets the table repository for this query
     *
     * @param TableRepository $repository
     * @return Builder
     */
    public function setRepository(TableRepository $repository): Builder
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * Gets the TableRepository for this query
     *
     * @return TableRepository
     */
    public function getRepository(): TableRepository
    {
        return $this->repository;
    }

    /**
     * @return Statement|void|null
     */
    public function getIterator()
    {

    }
}