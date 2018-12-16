<?php

namespace Phanda\Bear\Query;

use Phanda\Bear\Results\ResultSetDecorator;
use Phanda\Contracts\Bear\Entity\Entity as EntityContract;
use Phanda\Contracts\Bear\Query\ResultSet as ResultSetContract;
use Phanda\Contracts\Bear\Table\TableRepository;
use Phanda\Contracts\Database\Statement;
use Phanda\Database\Query\Query as DatabaseQueryBuilder;
use Phanda\Contracts\Bear\Query\Builder as QueryBuilderContract;
use Phanda\Dictionary\Iterator\MapReduceIterator;
use Phanda\Exceptions\Bear\EntityNotFoundException;

class Builder extends DatabaseQueryBuilder implements QueryBuilderContract, \JsonSerializable
{

    const OPERATION_PREPEND = 0;
    const OPERATION_APPEND = 1;
    const OPERATION_OVERWRITE = 2;

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
        return $this->all();
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
        if ($this->results !== null) {
            return $this->results;
        }

        $this->setResults($this->decorateQueryResults($this->executeQuery()));
        return $this->results;
    }

    /**
     * Returns an array representation of the current query
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->all()->toArray();
    }

    /**
     * Adds a map/reducer to the query to be executed when results
     * are fetched.
     *
     * @param callable $mapper
     * @param callable|null $reducer
     * @param bool $overwrite
     * @return Builder
     */
    public function addMapReducer(callable $mapper, ?callable $reducer = null, $overwrite = false): Builder
    {
        if ($overwrite) {
            $this->mapReduce = [];
        }

        $this->mapReduce[] = compact('mapper', 'reducer');
        return $this;
    }

    /**
     * Gets the map/reducers for the query
     *
     * @return array
     */
    public function getMapReducers(): array
    {
        return $this->mapReduce;
    }

    /**
     * @return callable[]
     */
    public function getQueryFormatters(): array
    {
        return $this->queryFormatters;
    }

    /**
     * Adds a query formatter that will be executed when the results are
     * fetched as part of this query.
     *
     * A formatter will get the first parameter being a Dictionary
     * of the results.
     *
     * @param callable $formatter
     * @param int $mode
     * @return Builder
     */
    public function addQueryFormatter(callable $formatter, $mode = self::OPERATION_PREPEND): Builder
    {
        if($mode === self::OPERATION_OVERWRITE) {
            $this->queryFormatters = [];
        }

        if ($mode === self::OPERATION_PREPEND) {
            array_unshift($this->queryFormatters, $formatter);
            return $this;
        }

        $this->queryFormatters[] = $formatter;
        return $this;
    }

    /**
     * Gets the first element of this query
     *
     * @return EntityContract|array|null
     */
    public function first()
    {
        if($this->dirty) {
            $this->limit(1);
        }

        return $this->all()->first();
    }

    /**
     * Gets the first element of this query, or fail if there is none
     *
     * @return EntityContract|array|null
     * @throws EntityNotFoundException
     */
    public function firstOrFail()
    {
        $entity = $this->first();

        if(!$entity) {
            // TODO: Implement saying what table name.
            //$table = $this->getRepository();
            throw new EntityNotFoundException("Entity not found");
        }

        return $entity;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Applies options to this query
     *
     * @param array $options
     * @return Builder
     */
    public function applyOptions(array $options): Builder
    {

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
        $decorator = ResultSetDecorator::class;

        foreach ($this->mapReduce as $functions) {
            $result = new MapReduceIterator($result, $functions['mapper'], $functions['reducer']);
        }

        if (!empty($this->mapReduce)) {
            $result = new $decorator($result);
        }

        foreach ($this->queryFormatters as $formatter) {
            $result = $formatter($result);
        }

        if (!empty($this->queryFormatters) && !($result instanceof $decorator)) {
            $result = new $decorator($result);
        }

        return $result;
    }
}