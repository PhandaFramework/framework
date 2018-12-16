<?php

namespace Phanda\Bear\Query;

use Phanda\Bear\Results\ResultSetDecorator;
use Phanda\Contracts\Bear\Entity\Entity as EntityContract;
use Phanda\Contracts\Bear\Query\ResultSet as ResultSetContract;
use Phanda\Contracts\Bear\Table\TableRepository;
use Phanda\Contracts\Database\Connection\Connection;
use Phanda\Contracts\Database\Statement;
use Phanda\Database\Query\Query as DatabaseQueryBuilder;
use Phanda\Contracts\Bear\Query\Builder as QueryBuilderContract;
use Phanda\Database\Query\Query;
use Phanda\Database\ValueBinder;
use Phanda\Dictionary\Iterator\MapReduceIterator;
use Phanda\Exceptions\Bear\Entity\EntityNotFoundException;
use RuntimeException;
use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;

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
     * @var bool
     */
    protected $hasFields;

    /**
     * @var bool
     */
    protected $autoFields = false;

    /**
     * Whether or not to convert results to Entities
     *
     * @var bool
     */
    protected $hydrate = true;

    /**
     * @var callable
     */
    protected $counter;

    /**
     * @var EagerLoader
     */
    protected $eagerLoader;

    /**
     * @var bool
     */
    protected $beforeFindEventFired = false;

    /**
     * @var int|null
     */
    protected $resultMysqlCount;

    /**
     * Builder constructor.
     *
     * @param Connection $connection
     * @param TableRepository $tableRepository
     */
    public function __construct(Connection $connection, TableRepository $tableRepository)
    {
        parent::__construct($connection);
        $this->setRepository($tableRepository);
    }

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
        if ($this->type !== self::TYPE_SELECT && $this->type !== null) {
            throw new RuntimeException(
                'You cannot call all() on a non-select query. Use execute() instead.'
            );
        }

        if ($this->results !== null) {
            return $this->results;
        }

        $this->setResults($this->decorateQueryResults($this->executeQuery()));
        return $this->results;
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
     * Gets the internal results of this query
     *
     * @return ResultSetContract|null
     */
    public function getResults(): ?ResultSetContract
    {
        return $this->results;
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
    public function setResults(ResultSetContract $results): QueryBuilderContract
    {
        $this->results = $results;
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
     * Sets the eager loaded status of this query
     *
     * @param bool $eagerLoaded
     * @return Builder
     */
    public function setEagerLoaded(bool $eagerLoaded): QueryBuilderContract
    {
        $this->eagerLoaded = $eagerLoaded;
        return $this;
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
     * Gets the first element of this query, or fail if there is none
     *
     * @return EntityContract|array|null
     * @throws EntityNotFoundException
     */
    public function firstOrFail()
    {
        $entity = $this->first();

        if (!$entity) {
            $table = $this->getRepository();
            throw new EntityNotFoundException("Entity not found in table: '{$table->getTableName()}'");
        }

        return $entity;
    }

    /**
     * Gets the first element of this query
     *
     * @return EntityContract|array|null
     */
    public function first()
    {
        if ($this->dirty) {
            $this->limit(1);
        }

        return $this->all()->first();
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
    public function applyOptions(array $options): QueryBuilderContract
    {
        $valid = [
            'fields' => 'select',
            'conditions' => 'where',
            'join' => 'join',
            'order' => 'order',
            'limit' => 'limit',
            'offset' => 'offset',
            'group' => 'group',
            'having' => 'having',
            'contain' => 'contain',
            'page' => 'page',
        ];

        ksort($options);

        foreach ($options as $option => $values) {
            if (isset($valid[$option], $values)) {
                $this->{$valid[$option]}($values);
            } else {
                $this->options[$option] = $values;
            }
        }

        return $this;
    }

    /**
     * Clones the ORM Query Builder
     */
    public function __clone()
    {
        parent::__clone();
        if($this->eagerLoader) {
            $this->eagerLoader = clone $this->eagerLoader;
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        if($this->resultMysqlCount === null) {
            $this->resultMysqlCount = $this->performCount();
        }

        return $this->resultMysqlCount;
    }

    /**
     * Performs a count(*) on the current query
     *
     * @return int
     */
    protected function performCount(): int
    {
        $query = $this->cleanCopy();
        $counter = $this->counter;

        if($counter) {
            $query->setCounter(null);
            return (int)$counter($query);
        }

        $complex = (
            $query->getClause('distinct') ||
            count($query->getClause('group')) ||
            count($query->getClause('union')) ||
            $query->getClause('having')
        );

        if (!$complex) {
            foreach ($query->getClause('select') as $field) {
                if ($field instanceof ExpressionContract) {
                    $complex = true;
                    break;
                }
            }
        }

        if (!$complex && $this->valueBinder !== null) {
            $order = $this->getClause('order');
            $complex = $order === null ? false : $order->hasNestedExpression();
        }

        $count = ['count' => 'count(*)'];

        if(!$complex) {
            $statement = $query->select($count, true)
                ->setAutoFields(false)
                ->execute();
        } else {
            $statement = $this->getConnection()
                ->newQuery()
                ->select($count)
                ->from(['count_source' => $query])
                ->execute();
        }

        $result = $statement->fetch(Statement::FETCH_TYPE_ASSOC)['count'];
        $statement->closeCursor();

        return (int)$result;
    }

    /**
     * Creates a clean copy of this query, to be used in sub queries.
     *
     * @return Builder
     */
    public function cleanCopy()
    {
        $clone = clone $this;
        $clone->setEagerLoader(clone $this->getEagerLoader());
        $clone->triggerBeforeFindEvent();
        $clone->setAutoFields(false);
        $clone->limit(null);
        $clone->offset(null);
        $clone->orderBy([], true);
        $clone->addMapReducer(null, null, true);
        $clone->addQueryFormatter(null, self::OPERATION_OVERWRITE);
        $clone->decorateResults(null, true);

        return $clone;
    }

    /**
     * @return EagerLoader
     */
    public function getEagerLoader(): EagerLoader
    {
        if(!$this->eagerLoader) {
            $this->eagerLoader = new EagerLoader();
        }

        return $this->eagerLoader;
    }

    /**
     * @param EagerLoader $eagerLoader
     * @return Builder
     */
    public function setEagerLoader(EagerLoader $eagerLoader): QueryBuilderContract
    {
        $this->eagerLoader = $eagerLoader;
        return $this;
    }

    public function triggerBeforeFindEvent()
    {

    }

    /**
     * @param bool $autoFields
     * @return Builder
     */
    public function setAutoFields(bool $autoFields): QueryBuilderContract
    {
        $this->autoFields = $autoFields;
        return $this;
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
    public function addMapReducer(callable $mapper, ?callable $reducer = null, $overwrite = false): QueryBuilderContract
    {
        if ($overwrite) {
            $this->mapReduce = [];
        }

        $this->mapReduce[] = compact('mapper', 'reducer');
        return $this;
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
    public function addQueryFormatter(callable $formatter, $mode = self::OPERATION_PREPEND): QueryBuilderContract
    {
        if ($mode === self::OPERATION_OVERWRITE) {
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
     * @param array $fields
     * @param bool $overwrite
     * @return Builder
     */
    public function select($fields = [], $overwrite = false): Query
    {
        return parent::select($fields, $overwrite);
    }

    /**
     * @return callable
     */
    public function getCounter(): callable
    {
        return $this->counter;
    }

    /**
     * @param callable|null $counter
     * @return Builder
     */
    public function setCounter(?callable $counter): QueryBuilderContract
    {
        $this->counter = $counter;
        return $this;
    }

    /**
     * Disables hydration on this query builder
     *
     * @return Builder
     */
    public function disableHydration(): QueryBuilderContract
    {
        return $this->enableHydration(false);
    }

    /**
     * Sets the hydration status of this query builder. (Whether or not to convert to entity)
     *
     * @param bool $hydrate
     * @return Builder
     */
    public function enableHydration(bool $hydrate = true): QueryBuilderContract
    {
        $this->makeDirty();
        $this->hydrate = $hydrate;
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function makeDirty()
    {
        $this->results = null;
        $this->resultMysqlCount = null;
        parent::makeDirty();
    }

    /**
     * Checks if hydration is enabled on this query builder
     *
     * @return bool
     */
    public function isHydrationEnabled(): bool
    {
        return $this->hydrate;
    }

    /**
     * @inheritdoc
     */
    public function toSql(ValueBinder $valueBinder = null): string
    {
        $this->triggerBeforeFindEvent();
        $this->transformQuery();
        return parent::toSql($valueBinder);
    }

    /**
     * Transforms the query by applying some default values
     */
    protected function transformQuery()
    {
        if (!$this->dirty || $this->type !== self::TYPE_SELECT) {
            return;
        }

        $repository = $this->getRepository();

        if(empty($this->queryKeywords['from'])) {
            $this->from([$repository->getAlias() => $repository->getTableName()]);
        }

        $this->selectDefaultFields();
    }

    /**
     * Adds the default selection fields to this query if none has been specified
     */
    protected function selectDefaultFields()
    {
        $select = $this->getClause('select');
        $this->hasFields = true;
        $repository = $this->getRepository();

        if(!count($select) || $this->isAutoFieldsEnabled()) {
            $this->hasFields = false;
            $this->select($repository->getSchema()->columns());
            $select = $this->getClause('select');
        }

        $aliased = $this->aliasFields($select, $repository->getAlias());
        $this->select($aliased, true);
    }

    /**
     * @return bool
     */
    public function isAutoFieldsEnabled(): bool
    {
        return $this->autoFields;
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
     * Gets the TableRepository for this query
     *
     * @return TableRepository
     */
    public function getRepository(): TableRepository
    {
        return $this->repository;
    }

    /**
     * Sets the table repository for this query
     *
     * @param TableRepository $repository
     * @return Builder
     */
    public function setRepository(TableRepository $repository): QueryBuilderContract
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * Apply custom finds against an existing query object.
     *
     * @param string $finder
     * @param array $options
     * @return Builder
     */
    public function find(string $finder, array $options = []): QueryBuilderContract
    {
        $table = $this->getRepository();

        return $table->callFinder($finder, $this, $options);
    }

    /**
     * @param null|string $table
     * @return Builder
     */
    public function update($table = null)
    {
        if(!$table) {
            $table = $this->getRepository()->getTableName();
        }

        parent::update($table);
        return $this;
    }

    /**
     * Deletes a record from the table
     *
     * The parameter $table is unused.
     *
     * @param string|null $table
     * @return Builder
     */
    public function delete(?string $table = null)
    {
        $repository = $this->getRepository();
        $this->from([$repository->getAlias() => $repository->getTableName()]);

        parent::delete();
        return $this;
    }

    /**
     * Inserts a record into a table
     *
     * @param array $columns
     * @return $this
     */
    public function insert(array $columns)
    {
        $repository = $this->getRepository();
        $this->into($repository->getTableName());

        parent::insert($columns);
        return $this;
    }
}