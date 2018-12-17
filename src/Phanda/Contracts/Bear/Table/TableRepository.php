<?php

namespace Phanda\Contracts\Bear\Table;

use Phanda\Contracts\Bear\Query\Builder as QueryBuilder;
use Phanda\Contracts\Bear\Entity\Entity as EntityContract;
use Phanda\Contracts\Database\Query\Query as DatabaseQueryContract;
use Phanda\Contracts\Database\Schema\TableSchema;
use Phanda\Database\Query\Expression\QueryExpression;
use Phanda\Exceptions\Bear\Entity\EntityNotFoundException;
use Phanda\Exceptions\Bear\Entity\EntityPersistenceException;

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
	 * Saves an entity in this Repository, with the fields that
	 * are marked dirty. Returns the same entity throw an exception
	 * on failure.
	 *
	 * @param EntityContract     $entity
	 * @param array|\ArrayAccess $options
	 * @return EntityContract|false
	 *
	 * @throws EntityPersistenceException
	 */
	public function saveEntityOrFail(EntityContract $entity, $options = []);

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
	 * Deletes an entity in this repository or fail.
	 *
	 * @param EntityContract     $entity
	 * @param array|\ArrayAccess $options
	 * @return bool
	 *
	 * @throws EntityPersistenceException
	 */
	public function deleteEntityOrFail(EntityContract $entity, $options = []): bool;

    /**
     * Creates a new Entity and its associated relations from
     * an array.
     *
     * The entity created will be separated from the table
     * until the save() function is called on the entity.
     *
     * @return EntityContract
     */
    public function newEntity(): EntityContract;

    /**
     * Returns the database table name.
     *
     * @return string
     */
    public function getTableName(): string;

    /**
     * Sets the database table name
     *
     * @param string $table
     * @return TableRepository
     */
    public function setTableName(string $table): TableRepository;

    /**
     * Calls a finder method directly and applies it to the passed query,
     * if no query is passed a new one will be created and returned
     *
     * @param string $type
     * @param QueryBuilder $query
     * @param array $options
     * @return QueryBuilder
     */
    public function callFinder(string $type, QueryBuilder $query, array $options = []): QueryBuilder;

    /**
     * @return TableSchema
     */
    public function getSchema();

    /**
     * @param $schema
     * @return $this
     */
    public function setSchema($schema);

}