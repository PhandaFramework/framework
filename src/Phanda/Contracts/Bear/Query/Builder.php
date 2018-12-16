<?php

namespace Phanda\Contracts\Bear\Query;

use Phanda\Bear\Query\EagerLoader;
use Phanda\Contracts\Bear\Table\TableRepository;
use Phanda\Contracts\Database\Query\Query as QueryContract;
use Phanda\Contracts\Bear\Query\ResultSet as ResultSetContract;
use Phanda\Database\ValueBinder;
use Phanda\Exceptions\Bear\Entity\EntityNotFoundException;
use Phanda\Contracts\Bear\Entity\Entity as EntityContract;

interface Builder extends QueryContract
{
    const OPERATION_PREPEND = 0;
    const OPERATION_APPEND = 1;
    const OPERATION_OVERWRITE = 2;

    /**
     * Specify data which should be serialized to JSON
     *
     * @return mixed
     */
    public function jsonSerialize();

    /**
     * Sets the table repository for this query
     *
     * @param TableRepository $repository
     * @return Builder
     */
    public function setRepository(TableRepository $repository): Builder;

    /**
     * Gets the TableRepository for this query
     *
     * @return TableRepository
     */
    public function getRepository(): TableRepository;

    /**
     * Sets the internal results of this query
     *
     * If this is set, the `execute()` function will be overridden,
     * and not be executed.
     *
     * @param ResultSetContract $results
     * @return Builder
     */
    public function setResults(ResultSetContract $results): Builder;

    /**
     * Gets the internal results of this query
     *
     * @return ResultSetContract|null
     */
    public function getResults(): ?ResultSetContract;

    /**
     * Sets the eager loaded status of this query
     *
     * @param bool $eagerLoaded
     * @return Builder
     */
    public function setEagerLoaded(bool $eagerLoaded): Builder;

    /**
     * Checks if the current query is eager loaded or not.
     *
     * @return bool
     */
    public function isEagerLoaded(): bool;

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
    public function aliasField(string $field, ?string $alias): array;

    /**
     * Sets the alias of multiple fields
     *
     * @param array $fields
     * @param string|null $defaultAlias
     * @return array
     */
    public function aliasFields(array $fields, ?string $defaultAlias): array;

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
    public function all(): ResultSetContract;

    /**
     * Returns an array representation of the current query
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Adds a map/reducer to the query to be executed when results
     * are fetched.
     *
     * @param callable $mapper
     * @param callable|null $reducer
     * @param bool $overwrite
     * @return Builder
     */
    public function addMapReducer(callable $mapper, ?callable $reducer = null, $overwrite = false): Builder;

    /**
     * Gets the map/reducers for the query
     *
     * @return array
     */
    public function getMapReducers(): array;

    /**
     * @return callable[]
     */
    public function getQueryFormatters(): array;

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
    public function addQueryFormatter(callable $formatter, $mode = self::OPERATION_PREPEND): Builder;

    /**
     * Gets the first element of this query
     *
     * @return EntityContract|array|null
     */
    public function first();

    /**
     * Gets the first element of this query, or fail if there is none
     *
     * @return EntityContract|array|null
     * @throws EntityNotFoundException
     */
    public function firstOrFail();

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * Applies options to this query
     *
     * @param array $options
     * @return Builder
     */
    public function applyOptions(array $options): Builder;

    /**
     * @param EagerLoader $eagerLoader
     * @return Builder
     */
    public function setEagerLoader(EagerLoader $eagerLoader): Builder;

    /**
     * @return EagerLoader
     */
    public function getEagerLoader(): EagerLoader;

    /**
     * Creates a clean copy of this query, to be used in sub queries.
     *
     * @return Builder
     */
    public function cleanCopy();

    public function triggerBeforeFindEvent();

    /**
     * @param bool $autoFields
     * @return Builder
     */
    public function setAutoFields(bool $autoFields): Builder;

    /**
     * @return bool
     */
    public function isAutoFieldsEnabled(): bool;

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @param callable|null $counter
     * @return Builder
     */
    public function setCounter(?callable $counter): Builder;

    /**
     * @return callable
     */
    public function getCounter(): callable;

    /**
     * @param array $fields
     * @param bool $overwrite
     * @return Builder
     */
    public function select($fields = [], $overwrite = false);

    /**
     * Sets the hydration status of this query builder. (Whether or not to convert to entity)
     *
     * @param bool $hydrate
     * @return Builder
     */
    public function enableHydration(bool $hydrate = true): Builder;

    /**
     * Disables hydration on this query builder
     *
     * @return Builder
     */
    public function disableHydration(): Builder;

    /**
     * Checks if hydration is enabled on this query builder
     *
     * @return bool
     */
    public function isHydrationEnabled(): bool;

    /**
     * @inheritdoc
     */
    public function toSql(ValueBinder $valueBinder = null): string;

    /**
     * Apply custom finds against an existing query object.
     *
     * @param string $finder
     * @param array $options
     * @return Builder
     */
    public function find(string $finder, array $options = []): Builder;

    /**
     * @param null|string $table
     * @return Builder
     */
    public function update($table = null);

    /**
     * Deletes a record from the table
     *
     * The parameter $table is unused.
     *
     * @param string|null $table
     * @return Builder
     */
    public function delete(?string $table = null);

    /**
     * Inserts a record into a table
     *
     * @param array $columns
     * @return Builder
     */
    public function insert(array $columns);
}