<?php

namespace Phanda\Bear\Query;

use Phanda\Contracts\Bear\Query\ResultSet as ResultSetContract;
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
     * @var ResultSetContract
     */
    protected $results;

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
     * Sets the internal results of this query
     *
     * If this is set, the `execute()` function will be overridden,
     * and not be executed.
     *
     * @param ResultSetContract $results
     * @return Builder
     */
    public function setResults(ResultSetContract $results): Builder
    {
        $this->results = $results;
        return $this;
    }

    /**
     * Gets the internal results of this query
     *
     * @return ResultSetContract|null
     */
    public function getResults(): ?ResultSetContract
    {
        return $this->results;
    }

    /**
     * Sets the eager loaded status of this query
     *
     * @param bool $eagerLoaded
     * @return Builder
     */
    public function setEagerLoaded(bool $eagerLoaded): Builder
    {
        $this->eagerLoaded = $eagerLoaded;
        return $this;
    }

    /**
     * Checks if the current query is eager loaded or not.
     *
     * @return bool
     */
    public function isEagerLoaded(): bool
    {
        return $this->eagerLoaded;
    }

    /**
     * Sets the alias of a field
     *
     * If a field is already aliased, it will not be
     * overridden by this function.
     *
     * @param string $field
     * @param string|null $alias
     * @return array
     */
    public function aliasField(string $field, ?string $alias): array
    {
        $namespaced = strpos($field, '.') !== false;
        $aliasedField = $field;

        if ($namespaced) {
            list($alias, $field) = explode('.', $field);
        }

        if (!$alias) {
            $alias = $this->getRepository()->getAlias();
        }

        $key = sprintf('%s__%s', $alias, $field);

        if (!$namespaced) {
            $aliasedField = $alias . '.' . $field;
        }

        return [$key => $aliasedField];
    }

    /**
     * Sets the alias of multiple fields
     *
     * @param array $fields
     * @param string|null $defaultAlias
     * @return array
     */
    public function aliasFields(array $fields, ?string $defaultAlias): array
    {
        $aliased = [];

        foreach ($fields as $alias => $field) {
            if (is_numeric($alias) && is_string($field)) {
                $aliased += $this->aliasField($field, $defaultAlias);
                continue;
            }
            $aliased[$alias] = $field;
        }

        return $aliased;
    }

    /**
     * Fetch all the results for this query
     *
     * If the internal results have been set prior, the execute
     * function will not be called and the results will be returned.
     *
     * @return ResultSetContract
     *
     * @todo: Implement caching here.
     */
    public function all(): ResultSetContract
    {
        if($this->results !== null) {
            return $this->results;
        }



        return $this->results;
    }

    /**
     * Executes the current query and returns the results
     *
     * @return ResultSetContract
     */
    protected function executeQuery(): ResultSetContract
    {
        // TODO: this
    }

    /**
     * Decorates the queries results
     *
     * @param \Traversable $result
     * @return ResultSetContract
     */
    protected function decorateQueryResults(\Traversable $result): ResultSetContract
    {

    }
}