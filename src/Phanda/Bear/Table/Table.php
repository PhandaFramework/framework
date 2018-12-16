<?php

namespace Phanda\Bear\Table;

use Phanda\Bear\Events\TableEvent;
use Phanda\Bear\Query\Builder;
use Phanda\Contracts\Bear\Entity\Entity as EntityContract;
use Phanda\Contracts\Bear\Query\Builder as QueryBuilder;
use Phanda\Contracts\Bear\Table\TableRepository;
use Phanda\Contracts\Database\Connection\Connection;
use Phanda\Contracts\Events\Dispatcher;
use Phanda\Database\Query\Expression\QueryExpression;
use Phanda\Database\Schema\TableSchema;
use Phanda\Exceptions\Bear\EntityNotFoundException;
use Phanda\Support\PhandaInflector;

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

    /**
     * @var Dispatcher
     */
    protected $eventDispatcher;

    /**
     * @var TableSchema
     */
    protected $schema;

    /**
     * Table constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!empty($config['registry_alias'])) {
            $this->setRegistryAlias($config['registry_alias']);
        }

        if (!empty($config['table'])) {
            $this->setTableName($config['table']);
        }

        if (!empty($config['alias'])) {
            $this->setAlias($config['alias']);
        }

        if (!empty($config['schema'])) {
            $this->setSchema($config['schema']);
        }

        if (!empty($config['connection'])) {
            $this->setConnection($config['connection']);
        }

        if (!empty($config['entity_class'])) {
            $this->setEntityClass($config['entity_class']);
        }

        if (!empty($config['event_dispatcher'])) {
            $this->setEventDispatcher($config['event_dispatcher']);
        }

        $this->initialize($config);
        $this->dispatchEvent('table.initialize');
    }

    /**
     * Initialize a table instance. Called after the constructor.
     *
     * You can use this method to define associations, attach behaviors
     * define validation and do any other initialization logic you need.
     *
     * @param array $config
     * @return void
     */
    public function initialize(array $config)
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
        $this->alias = $alias;
        return $this;
    }

    /**
     * Gets the alias of the table
     *
     * @return string
     */
    public function getAlias(): string
    {
        if ($this->alias === null) {
            $alias = stripNamespace(get_class($this));
            $alias = substr($alias, 0, -5) ?: $this->table;
            $this->alias = $alias;
        }

        return $this->alias;
    }

    /**
     * Sets the alias of the registry
     *
     * @param string $alias
     * @return TableRepository
     */
    public function setRegistryAlias(string $alias): TableRepository
    {
        $this->registryAlias = $alias;
        return $this;
    }

    /**
     * Gets the alias of the registry
     *
     * @return string
     */
    public function getRegistryAlias(): string
    {
        if ($this->registryAlias === null) {
            $this->registryAlias = $this->getAlias();
        }

        return $this->registryAlias;
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
        return new Builder($this->getConnection(), $this);
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
        if ($this->table === null) {
            $table = stripNamespace(get_class($this));
            $table = substr($table, 0, -5);
            if (!$table) {
                $table = $this->getAlias();
            }
            $this->table = PhandaInflector::underscore($table);
        }

        return $this->table;
    }

    /**
     * Sets the database table name
     *
     * @param string $table
     * @return TableRepository
     */
    public function setTableName(string $table): TableRepository
    {
        $this->table = $table;
        return $this;
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

    /**
     * @param Connection $connection
     * @return Table
     */
    public function setConnection(Connection $connection): Table
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        if ($this->connection === null) {
            $this->connection = phanda()->create(Connection::class);
        }

        return $this->connection;
    }

    /**
     * @param array|string $primaryKey
     * @return Table
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * @return array|string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param string $displayRow
     * @return Table
     */
    public function setDisplayRow(string $displayRow): Table
    {
        $this->displayRow = $displayRow;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayRow(): string
    {
        return $this->displayRow;
    }

    /**
     * @param string $entityClass
     * @return Table
     */
    public function setEntityClass(string $entityClass): Table
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param Dispatcher $eventDispatcher
     * @return Table
     */
    public function setEventDispatcher(Dispatcher $eventDispatcher): Table
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return Dispatcher
     */
    public function getEventDispatcher(): Dispatcher
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = phanda()->create(Dispatcher::class);
        }

        return $this->eventDispatcher;
    }

    /**
     * Helper function that helps to dispatch table events
     *
     * @param string $name
     * @param array|null $data
     * @return TableEvent
     */
    public function dispatchEvent(string $name, ?array $data = null): TableEvent
    {
        $eventDispatcher = $this->getEventDispatcher();
        $event = new TableEvent($this, $data);
        $eventDispatcher->dispatch($name, $event);
        return $event;
    }

    /**
     * Aliases a field with the current table name/alias
     *
     * If the field is already aliased, it gets ignored.
     *
     * @param string $field
     * @return string
     */
    public function aliasField(string $field): string
    {
        if (strpos($field, '.') !== false) {
            return $field;
        }

        return $this->getAlias() . '.' . $field;
    }

    /**
     * @return TableSchema
     *
     * @throws \Phanda\Exceptions\Database\Schema\SchemaException
     */
    public function getSchema()
    {
        if ($this->schema === null) {
            $this->schema = $this->initializeSchema(
                $this->getConnection()
                    ->getSchemaCollection()
                    ->describe($this->getTableName())
            );
        }

        return $this->schema;
    }

    /**
     * @param $schema
     * @return $this
     *
     * @throws \Phanda\Exceptions\Database\Schema\SchemaException
     */
    public function setSchema($schema)
    {
        if (is_array($schema)) {
            $constraints = [];

            if (isset($schema['_constraints'])) {
                $constraints = $schema['_constraints'];
                unset($schema['_constraints']);
            }

            $schema = new TableSchema($this->getTableName(), $schema);

            foreach ($constraints as $name => $value) {
                $schema->addConstraint($name, $value);
            }
        }

        $this->schema = $schema;
        return $this;
    }

    /**
     * This function can be extended in children classes to
     * modify the table schema.
     *
     * @param TableSchema $schema
     * @return TableSchema
     */
    protected function initializeSchema(TableSchema $schema): TableSchema
    {
        return $schema;
    }
}