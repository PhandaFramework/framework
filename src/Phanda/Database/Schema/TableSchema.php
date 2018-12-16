<?php

namespace Phanda\Database\Schema;

use Phanda\Exceptions\Database\Schema\SchemaException as Exception;
use Phanda\Contracts\Database\Connection\Connection;
use Phanda\Contracts\Database\Schema\TableSchema as TableSchemaContract;
use Phanda\Contracts\Database\Schema\SqlGenerator as SqlGeneratorContract;

class TableSchema implements TableSchemaContract, SqlGeneratorContract
{

    /**
     * Valid column length that can be used with text type columns
     *
     * @var array
     */
    public static $columnLengths = [
        'tiny' => self::LENGTH_TINY,
        'medium' => self::LENGTH_MEDIUM,
        'long' => self::LENGTH_LONG
    ];

    /**
     * The valid keys that can be used in a column
     * definition.
     *
     * @var array
     */
    protected static $columnKeys = [
        'type' => null,
        'baseType' => null,
        'length' => null,
        'precision' => null,
        'null' => null,
        'default' => null,
        'comment' => null,
    ];

    /**
     * Additional type specific properties.
     *
     * @var array
     */
    protected static $columnExtras = [
        'string' => [
            'fixed' => null,
            'collate' => null,
        ],
        'text' => [
            'collate' => null,
        ],
        'tinyinteger' => [
            'unsigned' => null,
        ],
        'smallinteger' => [
            'unsigned' => null,
        ],
        'integer' => [
            'unsigned' => null,
            'autoIncrement' => null,
        ],
        'biginteger' => [
            'unsigned' => null,
            'autoIncrement' => null,
        ],
        'decimal' => [
            'unsigned' => null,
        ],
        'float' => [
            'unsigned' => null,
        ],
    ];

    /**
     * The valid keys that can be used in an index
     * definition.
     *
     * @var array
     */
    protected static $indexKeys = [
        'type' => null,
        'columns' => [],
        'length' => [],
        'references' => [],
        'update' => 'restrict',
        'delete' => 'restrict',
    ];

    /**
     * Names of the valid index types.
     *
     * @var array
     */
    protected static $validIndexTypes = [
        self::INDEX_INDEX,
        self::INDEX_FULLTEXT,
    ];

    /**
     * Names of the valid constraint types.
     *
     * @var array
     */
    protected static $validConstraintTypes = [
        self::CONSTRAINT_PRIMARY,
        self::CONSTRAINT_UNIQUE,
        self::CONSTRAINT_FOREIGN,
    ];

    /**
     * Names of the valid foreign key actions.
     *
     * @var array
     */
    protected static $validForeignKeyActions = [
        self::ACTION_CASCADE,
        self::ACTION_SET_NULL,
        self::ACTION_SET_DEFAULT,
        self::ACTION_NO_ACTION,
        self::ACTION_RESTRICT,
    ];

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var array
     */
    protected $typeMap = [];

    /**
     * @var array
     */
    protected $indexes = [];

    /**
     * @var array
     */
    protected $constraints = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var bool
     */
    protected $temporary = false;

    /**
     * TableSchema constructor.
     *
     * @param string $table
     * @param array $columns
     */
    public function __construct(string $table, array $columns = [])
    {
        $this->setTable($table);

        foreach ($columns as $field => $definition) {
            $this->addColumn($field, $definition);
        }
    }

    /**
     * Generate the SQL to create the table
     *
     * @param Connection $connection
     * @return array
     */
    public function createSql(Connection $connection): array
    {
        $dialect = $connection->getDriver()->getSchemaDialect();
        $columns = $constraints = $indexes = [];

        foreach (array_keys($this->columns) as $name) {
            $columns[] = $dialect->columnSql($this, $name);
        }

        foreach (array_keys($this->constraints) as $name) {
            $constraints[] = $dialect->constraintSql($this, $name);
        }

        foreach (array_keys($this->indexes) as $name) {
            $indexes[] = $dialect->indexSql($this, $name);
        }

        return $dialect->createTableSql($this, $columns, $constraints, $indexes);
    }

    /**
     * Generate the SQL to drop a table
     *
     * @param Connection $connection
     * @return array
     */
    public function dropSql(Connection $connection): array
    {
        $dialect = $connection->getDriver()->getSchemaDialect();
        return $dialect->dropTableSql($this);
    }

    /**
     * Generate the SQL truncate a table
     *
     * @param Connection $connection
     * @return array
     */
    public function truncateSql(Connection $connection): array
    {
        $dialect = $connection->getDriver()->getSchemaDialect();
        return $dialect->truncateTableSql($this);
    }

    /**
     * Generate the SQL to add a constraint to the table
     *
     * @param Connection $connection
     * @return array
     */
    public function addConstraintSql(Connection $connection): array
    {
        $dialect = $connection->getDriver()->getSchemaDialect();
        return $dialect->addConstraintSql($this);
    }

    /**
     * Generate the SQL to drop a constraint on the table
     *
     * @param Connection $connection
     * @return array
     */
    public function dropConstraintSql(Connection $connection): array
    {
        $dialect = $connection->getDriver()->getSchemaDialect();
        return $dialect->dropConstraintSql($this);
    }

    /**
     * Check whether or not a table has an autoIncrement column defined.
     *
     * @return bool
     */
    public function hasAutoincrement(): bool
    {
        foreach ($this->columns as $column) {
            if (isset($column['autoIncrement']) && $column['autoIncrement']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets whether the table is temporary in the database.
     *
     * @return bool
     */
    public function isTemporary(): bool
    {
        return $this->temporary;
    }

    /**
     * Sets whether the table is temporary in the database.
     *
     * @param bool $temporary
     * @return TableSchemaContract
     */
    public function setTemporary(bool $temporary): TableSchemaContract
    {
        $this->temporary = $temporary;
        return $this;
    }

    /**
     * Get the column(s) used for the primary key.
     *
     * @return array
     */
    public function primaryKey(): array
    {
        foreach ($this->constraints as $name => $data) {
            if ($data['type'] === static::CONSTRAINT_PRIMARY) {
                return $data['columns'];
            }
        }

        return [];
    }

    /**
     * Used to add indexes, and full text indexes in platforms that support
     * them.
     *
     * @param string $name
     * @param array $attrs
     * @return TableSchemaContract
     *
     * @throws Exception
     */
    public function addIndex(string $name, array $attrs): TableSchemaContract
    {
        if (is_string($attrs)) {
            $attrs = ['type' => $attrs];
        }

        $attrs = array_intersect_key($attrs, static::$indexKeys);
        $attrs += static::$indexKeys;
        unset($attrs['references'], $attrs['update'], $attrs['delete']);

        if (!in_array($attrs['type'], static::$validIndexTypes, true)) {
            throw new Exception(sprintf('Invalid index type "%s" in index "%s" in table "%s".', $attrs['type'], $name, $this->table));
        }

        if (empty($attrs['columns'])) {
            throw new Exception(sprintf('Index "%s" in table "%s" must have at least one column.', $name, $this->table));
        }

        $attrs['columns'] = (array)$attrs['columns'];

        foreach ($attrs['columns'] as $field) {
            if (empty($this->columns[$field])) {
                $msg = sprintf(
                    'Columns used in index "%s" in table "%s" must be added to the Table schema first. ' .
                    'The column "%s" was not found.',
                    $name,
                    $this->table,
                    $field
                );
                throw new Exception($msg);
            }
        }

        $this->indexes[$name] = $attrs;
        return $this;
    }

    /**
     * Read information about an index based on name.
     *
     * @param string $name
     * @return array|null
     */
    public function getIndex(string $name): ?array
    {
        if (!isset($this->indexes[$name])) {
            return null;
        }

        return $this->indexes[$name];
    }

    /**
     * Get the names of all the indexes in the table.
     *
     * @return string[]
     */
    public function indexes(): array
    {
        return array_keys($this->indexes);
    }

    /**
     * Used to add constraints to a table. For example primary keys, unique
     * keys and foreign keys.
     *
     * @param string $name
     * @param array $attrs
     * @return TableSchemaContract
     *
     * @throws Exception
     */
    public function addConstraint(string $name, array $attrs): TableSchemaContract
    {
        if (is_string($attrs)) {
            $attrs = ['type' => $attrs];
        }

        $attrs = array_intersect_key($attrs, static::$indexKeys);
        $attrs += static::$indexKeys;

        if (!in_array($attrs['type'], static::$validConstraintTypes, true)) {
            throw new Exception(sprintf('Invalid constraint type "%s" in table "%s".', $attrs['type'], $this->table));
        }

        if (empty($attrs['columns'])) {
            throw new Exception(sprintf('Constraints in table "%s" must have at least one column.', $this->table));
        }

        $attrs['columns'] = (array)$attrs['columns'];

        foreach ($attrs['columns'] as $field) {
            if (empty($this->columns[$field])) {
                $msg = sprintf(
                    'Columns used in constraints must be added to the Table schema first. ' .
                    'The column "%s" was not found in table "%s".',
                    $field,
                    $this->table
                );
                throw new Exception($msg);
            }
        }

        if ($attrs['type'] === static::CONSTRAINT_FOREIGN) {
            $attrs = $this->checkForeignKey($attrs);

            if (isset($this->constraints[$name])) {
                $this->constraints[$name]['columns'] = array_unique(array_merge(
                    $this->constraints[$name]['columns'],
                    $attrs['columns']
                ));

                if (isset($this->constraints[$name]['references'])) {
                    $this->constraints[$name]['references'][1] = array_unique(array_merge(
                        (array)$this->constraints[$name]['references'][1],
                        [$attrs['references'][1]]
                    ));
                }

                return $this;
            }
        } else {
            unset($attrs['references'], $attrs['update'], $attrs['delete']);
        }

        $this->constraints[$name] = $attrs;
        return $this;
    }

    /**
     * Helper method to check/validate foreign keys.
     *
     * @param array $attrs Attributes to set.
     * @return array
     *
     * @throws Exception
     */
    protected function checkForeignKey($attrs)
    {
        if (count($attrs['references']) < 2) {
            throw new Exception('References must contain a table and column.');
        }

        if (!in_array($attrs['update'], static::$validForeignKeyActions)) {
            throw new Exception(sprintf('Update action is invalid. Must be one of %s', implode(',', static::$validForeignKeyActions)));
        }

        if (!in_array($attrs['delete'], static::$validForeignKeyActions)) {
            throw new Exception(sprintf('Delete action is invalid. Must be one of %s', implode(',', static::$validForeignKeyActions)));
        }

        return $attrs;
    }

    /**
     * Read information about a constraint based on name.
     *
     * @param string $name
     * @return array|null
     */
    public function getConstraint(string $name): ?array
    {
        if (!isset($this->constraints[$name])) {
            return null;
        }

        return $this->constraints[$name];
    }

    /**
     * Remove a constraint.
     *
     * @param string $name
     * @return TableSchemaContract
     */
    public function dropConstraint(string $name): TableSchemaContract
    {
        if (isset($this->constraints[$name])) {
            unset($this->constraints[$name]);
        }

        return $this;
    }

    /**
     * Get the names of all the constraints in the table.
     *
     * @return string[]
     */
    public function constraints(): array
    {
        return array_keys($this->constraints);
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     * @return TableSchema
     */
    public function setTable(string $table): TableSchema
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Get the name of the table.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->table;
    }

    /**
     * Add a column to the table.
     *
     * @param string $name
     * @param array|string $attrs
     * @return $this
     */
    public function addColumn(string $name, $attrs)
    {
        if (is_string($attrs)) {
            $attrs = ['type' => $attrs];
        }
        $valid = static::$columnKeys;
        if (isset(static::$columnExtras[$attrs['type']])) {
            $valid += static::$columnExtras[$attrs['type']];
        }
        $attrs = array_intersect_key($attrs, $valid);
        $this->columns[$name] = $attrs + $valid;
        $this->typeMap[$name] = $this->columns[$name]['type'];

        return $this;
    }

    /**
     * Get column data in the table.
     *
     * @param string $name
     * @return array|null
     */
    public function getColumn(string $name): ?array
    {
        if (!isset($this->columns[$name])) {
            return null;
        }

        $column = $this->columns[$name];
        unset($column['baseType']);
        return $column;
    }

    /**`
     * Returns true if a column exists in the schema.
     *
     * @param string $name Column name.
     * @return bool
     */
    public function hasColumn(string $name): bool
    {
        return isset($this->columns[$name]);
    }

    /**
     * Remove a column from the table schema.
     *
     * @param string $name
     * @return $this
     */
    public function removeColumn(string $name)
    {
        unset($this->columns[$name], $this->typeMap[$name]);
        return $this;
    }

    /**
     * Get the column names in the table.
     *
     * @return string[]
     */
    public function columns(): array
    {
        return array_keys($this->columns);
    }

    /**
     * Returns column type or null if a column does not exist.
     *
     * @param string $name
     * @return string|null
     */
    public function getColumnType(string $name): ?string
    {
        if (!isset($this->columns[$name])) {
            return null;
        }

        return $this->columns[$name]['type'];
    }

    /**
     * Sets the type of a column.
     *
     * @param string $name
     * @param string $type
     * @return $this
     */
    public function setColumnType(string $name, string $type)
    {
        if (!isset($this->columns[$name])) {
            return $this;
        }

        $this->columns[$name]['type'] = $type;
        $this->typeMap[$name] = $type;
        return $this;
    }

    /**
     * Check whether or not a field is nullable
     *
     * @param string $name
     * @return bool
     */
    public function isNullable(string $name): bool
    {
        if (!isset($this->columns[$name])) {
            return true;
        }

        return ($this->columns[$name]['null'] === true);
    }

    /**
     * Returns an array where the keys are the column names in the schema
     * and the values the database type they have.
     *
     * @return array
     */
    public function typeMap(): array
    {
        return $this->typeMap;
    }

    /**
     * Get a hash of columns and their default values.
     *
     * @return array
     */
    public function defaultValues(): array
    {
        $defaults = [];
        foreach ($this->columns as $name => $data) {
            if (!array_key_exists('default', $data)) {
                continue;
            }
            if ($data['default'] === null && $data['null'] !== true) {
                continue;
            }
            $defaults[$name] = $data['default'];
        }

        return $defaults;
    }

    /**
     * Gets the options for a table.
     *
     * Table options allow you to set platform specific table level options.
     * For example the engine type in MySQL.
     *
     * @return array An array of options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Sets the options for a table.
     *
     * Table options allow you to set platform specific table level options.
     * For example the engine type in MySQL.
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }
}