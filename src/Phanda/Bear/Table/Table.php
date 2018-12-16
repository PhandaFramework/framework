<?php

namespace Phanda\Bear\Table;

use Phanda\Contracts\Bear\Entity\Entity as EntityContract;
use Phanda\Contracts\Bear\Query\Builder as QueryBuilder;
use Phanda\Contracts\Bear\Table\TableRepository;
use Phanda\Contracts\Database\Connection\Connection;
use Phanda\Database\Query\Expression\QueryExpression;
use Phanda\Exceptions\Bear\EntityNotFoundException;

class Table implements TableRepository
{

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string|array
     */
    protected $primaryKey;

    /**
     * @var string
     */
    protected $displayRow;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var string
     */
    protected $registryAlias;

    public function __construct()
    {

    }

    public function setConfiguration(array $configuration = []): TableRepository
    {

    }

    /**
     * Sets the alias of the table
     *
     * @param string $alias
     * @return TableRepository
     */
    public function setAlias(string $alias): TableRepository
    {
        // TODO: Implement setAlias() method.
    }

    /**
     * Gets the alias of the table
     *
     * @return string
     */
    public function getAlias(): string
    {
        // TODO: Implement getAlias() method.
    }

    /**
     * Sets the alias of the registry
     *
     * @param string $alias
     * @return TableRepository
     */
    public function setRegistryAlias(string $alias): TableRepository
    {
        // TODO: Implement setRegistryAlias() method.
    }

    /**
     * Gets the alias of the registry
     *
     * @return string
     */
    public function getRegistryAlias(): string
    {
        // TODO: Implement getRegistryAlias() method.
    }

    /**
     * Checks if the repository contains a field/column
     *
     * @param string $field
     * @return bool
     */
    public function hasField(string $field): bool
    {
        // TODO: Implement hasField() method.
    }

    /**
     * Starts a query builder on this repository
     *
     * @param string $type
     * @param array|\ArrayAccess $options
     * @return QueryBuilder
     */
    public function find(string $type = 'all', $options = []): QueryBuilder
    {
        // TODO: Implement find() method.
    }

    /**
     * Gets an entity by its primary key
     *
     * @param mixed $primaryKey
     * @param array|\ArrayAccess $options
     * @return EntityContract|null
     *
     * @see TableRepository::find()
     */
    public function get($primaryKey, $options = []): ?EntityContract
    {
        // TODO: Implement get() method.
    }

    /**
     * Gets an entity by its primary key, or throw an exception if
     * no entity with the primary key exists.
     *
     * @param mixed $primaryKey
     * @param array|\ArrayAccess $options
     * @return EntityContract
     *
     * @throws EntityNotFoundException
     *
     * @see TableRepository::get()
     */
    public function getOrFail($primaryKey, $options = []): EntityContract
    {
        // TODO: Implement getOrFail() method.
    }

    /**
     * Starts a Query on this repository
     *
     * @return QueryBuilder
     */
    public function query(): QueryBuilder
    {
        // TODO: Implement query() method.
    }

    /**
     * Updates all the values in this repository that matches
     * the given fields and conditions.
     *
     * Returns the amount of affected rows.
     *
     * @param string|array|callable|QueryExpression $fields
     * @param mixed $conditions
     * @return int
     *
     * @see DatabaseQueryContract::where() for available conditions.
     */
    public function updateAll($fields, $conditions): int
    {
        // TODO: Implement updateAll() method.
    }

    /**
     * Deletes all the values in this repository that matches
     * the given conditions
     *
     * Returns the amount of deleted rows
     *
     * @param mixed $conditions
     * @return int
     *
     * @see DatabaseQueryContract::where() for available conditions.
     */
    public function deleteAll($conditions): int
    {
        // TODO: Implement deleteAll() method.
    }

    /**
     * Returns true if the current repository contains a record
     * that matches the given conditions
     *
     * @param array|\ArrayAccess $conditions
     * @return bool
     */
    public function exists($conditions): bool
    {
        // TODO: Implement exists() method.
    }

    /**
     * Saves an entity in this Repository, with the fields that
     * are marked dirty. Returns the same entity or false on
     * failure
     *
     * @param EntityContract $entity
     * @param array|\ArrayAccess $options
     * @return EntityContract|false
     */
    public function saveEntity(EntityContract $entity, $options = [])
    {
        // TODO: Implement saveEntity() method.
    }

    /**
     * Deletes an entity in this repository.
     *
     * If the Entity has any relations it will go through and
     * delete them as well, depending on the options that are
     * passed to this function.
     *
     * @param EntityContract $entity
     * @param array|\ArrayAccess $options
     * @return bool
     */
    public function deleteEntity(EntityContract $entity, $options = []): bool
    {
        // TODO: Implement deleteEntity() method.
    }

    /**
     * Creates a new Entity and its associated relations from
     * an array.
     *
     * The entity created will be separated from the table
     * until the save() function is called on the entity.
     *
     * @param array|null $data
     * @param array $options
     * @return EntityContract
     */
    public function newEntity(?array $data = null, array $options = []): EntityContract
    {
        // TODO: Implement newEntity() method.
    }

    /**
     * Creates entities and their associated relations from
     * an array.
     *
     * The entities created will be separated from the table
     * until the save() function is called on each entity.
     *
     * @param array $data
     * @param array $options
     * @return EntityContract[]
     */
    public function newEntities(array $data, array $options = []): array
    {
        // TODO: Implement newEntities() method.
    }

    /**
     * Updates a given entity with the provided data.
     *
     * @param EntityContract $entity
     * @param array $data
     * @param array $options
     * @return EntityContract
     */
    public function updateEntity(EntityContract $entity, array $data, array $options = []): EntityContract
    {
        // TODO: Implement updateEntity() method.
    }

    /**
     * Updates the given entities with the provided data.
     *
     * @param EntityContract[] $entities
     * @param array $data
     * @param array $options
     * @return EntityContract[]
     */
    public function updateEntities(array $entities, array $data, array $options = []): array
    {
        // TODO: Implement updateEntities() method.
    }

    /**
     * Returns the database table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        // TODO: Implement getTableName() method.
    }

    /**
     * Sets the database table name
     *
     * @param string $table
     * @return TableRepository
     */
    public function setTableName(string $table): TableRepository
    {
        // TODO: Implement setTableName() method.
    }

    /**
     * Calls a finder method directly and applies it to the passed query,
     * if no query is passed a new one will be created and returned
     *
     * @param string $type
     * @param QueryBuilder $query
     * @param array $options
     * @return QueryBuilder
     */
    public function callFinder(string $type, QueryBuilder $query, array $options = []): QueryBuilder
    {
        // TODO: Implement callFinder() method.
    }
}