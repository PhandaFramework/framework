<?php

namespace Phanda\Contracts\Bear\Table;

use Phanda\Contracts\Bear\Query\Builder as QueryBuilder;
use Phanda\Contracts\Bear\Entity\Entity as EntityContract;
use Phanda\Contracts\Database\Query\Query as DatabaseQueryContract;
use Phanda\Database\Query\Expression\QueryExpression;
use Phanda\Exceptions\Bear\EntityNotFoundException;

interface TableRepository
{

    /**
     * Sets the alias of the table
     *
     * @param string $alias
     * @return TableRepository
     */
    public function setAlias(string $alias): self;

    /**
     * Gets the alias of the table
     *
     * @return string
     */
    public function getAlias(): string;

    /**
     * Sets the alias of the registry
     *
     * @param string $alias
     * @return TableRepository
     */
    public function setRegistryAlias(string $alias): self;

    /**
     * Gets the alias of the registry
     *
     * @return string
     */
    public function getRegistryAlias(): string;

    /**
     * Checks if the repository contains a field/column
     *
     * @param string $field
     * @return bool
     */
    public function hasField(string $field): bool;

    /**
     * Starts a query builder on this repository
     *
     * @param string $type
     * @param array|\ArrayAccess $options
     * @return QueryBuilder
     */
    public function find(string $type = 'all', $options = []): QueryBuilder;

    /**
     * Gets an entity by its primary key
     *
     * @param mixed $primaryKey
     * @param array|\ArrayAccess $options
     * @return EntityContract|null
     *
     * @see TableRepository::find()
     */
    public function get($primaryKey, $options = []): ?EntityContract;

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
    public function getOrFail($primaryKey, $options = []): EntityContract;

    /**
     * Starts a Query on this repository
     *
     * @return QueryBuilder
     */
    public function query(): QueryBuilder;

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
    public function updateAll($fields, $conditions): int;

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
    public function deleteAll($conditions): int;

    /**
     * Returns true if the current repository contains a record
     * that matches the given conditions
     *
     * @param array|\ArrayAccess $conditions
     * @return bool
     */
    public function exists($conditions): bool;

    /**
     * Saves an entity in this Repository, with the fields that
     * are marked dirty. Returns the same entity or false on
     * failure
     *
     * @param EntityContract $entity
     * @param array|\ArrayAccess $options
     * @return EntityContract|false
     */
    public function saveEntity(EntityContract $entity, $options = []);

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
    public function deleteEntity(EntityContract $entity, $options = []): bool;

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
    public function newEntity(?array $data = null, array $options = []): EntityContract;

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
    public function newEntities(array $data, array $options = []): array;

    /**
     * Updates a given entity with the provided data.
     *
     * @param EntityContract $entity
     * @param array $data
     * @param array $options
     * @return EntityContract
     */
    public function updateEntity(EntityContract $entity, array $data, array $options = []): EntityContract;

    /**
     * Updates the given entities with the provided data.
     *
     * @param EntityContract[] $entities
     * @param array $data
     * @param array $options
     * @return EntityContract[]
     */
    public function updateEntities(array $entities, array $data, array $options = []): array;

}