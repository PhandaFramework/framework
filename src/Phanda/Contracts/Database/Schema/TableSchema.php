<?php

namespace Phanda\Contracts\Database\Schema;

interface TableSchema extends Schema
{
    /**
     * Binary column type
     *
     * @var string
     */
    const TYPE_BINARY = 'binary';

    /**
     * Binary UUID column type
     *
     * @var string
     */
    const TYPE_BINARY_UUID = 'binaryuuid';

    /**
     * Date column type
     *
     * @var string
     */
    const TYPE_DATE = 'date';

    /**
     * Datetime column type
     *
     * @var string
     */
    const TYPE_DATETIME = 'datetime';

    /**
     * Time column type
     *
     * @var string
     */
    const TYPE_TIME = 'time';

    /**
     * Timestamp column type
     *
     * @var string
     */
    const TYPE_TIMESTAMP = 'timestamp';

    /**
     * JSON column type
     *
     * @var string
     */
    const TYPE_JSON = 'json';

    /**
     * String column type
     *
     * @var string
     */
    const TYPE_STRING = 'string';

    /**
     * Text column type
     *
     * @var string
     */
    const TYPE_TEXT = 'text';

    /**
     * Tiny Integer column type
     *
     * @var string
     */
    const TYPE_TINYINTEGER = 'tinyinteger';

    /**
     * Small Integer column type
     *
     * @var string
     */
    const TYPE_SMALLINTEGER = 'smallinteger';

    /**
     * Integer column type
     *
     * @var string
     */
    const TYPE_INTEGER = 'integer';

    /**
     * Big Integer column type
     *
     * @var string
     */
    const TYPE_BIGINTEGER = 'biginteger';

    /**
     * Float column type
     *
     * @var string
     */
    const TYPE_FLOAT = 'float';

    /**
     * Decimal column type
     *
     * @var string
     */
    const TYPE_DECIMAL = 'decimal';

    /**
     * Boolean column type
     *
     * @var string
     */
    const TYPE_BOOLEAN = 'boolean';

    /**
     * UUID column type
     *
     * @var string
     */
    const TYPE_UUID = 'uuid';

    /**
     * Column length when using a `tiny` column type
     *
     * @var int
     */
    const LENGTH_TINY = 255;

    /**
     * Column length when using a `medium` column type
     *
     * @var int
     */
    const LENGTH_MEDIUM = 16777215;

    /**
     * Column length when using a `long` column type
     *
     * @var int
     */
    const LENGTH_LONG = 4294967295;

    /**
     * Primary constraint type
     *
     * @var string
     */
    const CONSTRAINT_PRIMARY = 'primary';

    /**
     * Unique constraint type
     *
     * @var string
     */
    const CONSTRAINT_UNIQUE = 'unique';

    /**
     * Foreign constraint type
     *
     * @var string
     */
    const CONSTRAINT_FOREIGN = 'foreign';

    /**
     * Index - index type
     *
     * @var string
     */
    const INDEX_INDEX = 'index';

    /**
     * Fulltext index type
     *
     * @var string
     */
    const INDEX_FULLTEXT = 'fulltext';

    /**
     * Foreign key cascade action
     *
     * @var string
     */
    const ACTION_CASCADE = 'cascade';

    /**
     * Foreign key set null action
     *
     * @var string
     */
    const ACTION_SET_NULL = 'setNull';

    /**
     * Foreign key no action
     *
     * @var string
     */
    const ACTION_NO_ACTION = 'noAction';

    /**
     * Foreign key restrict action
     *
     * @var string
     */
    const ACTION_RESTRICT = 'restrict';

    /**
     * Foreign key restrict default
     *
     * @var string
     */
    const ACTION_SET_DEFAULT = 'setDefault';

    /**
     * Check whether or not a table has an autoIncrement column defined.
     *
     * @return bool
     */
    public function hasAutoincrement(): bool;

    /**
     * Sets whether the table is temporary in the database.
     *
     * @param bool $temporary
     * @return $this
     */
    public function setTemporary(bool $temporary): TableSchema;

    /**
     * Gets whether the table is temporary in the database.
     *
     * @return bool
     */
    public function isTemporary(): bool;

    /**
     * Get the column(s) used for the primary key.
     *
     * @return array
     */
    public function primaryKey(): array;

    /**
     * Used to add indexes, and full text indexes in platforms that support
     * them.
     *
     * @param string $name
     * @param array $attrs
     * @return $this
     */
    public function addIndex(string $name, array $attrs): TableSchema;

    /**
     * Read information about an index based on name.
     *
     * @param string $name
     * @return array|null
     */
    public function getIndex(string $name): ?array;

    /**
     * Get the names of all the indexes in the table.
     *
     * @return string[]
     */
    public function indexes(): array;

    /**
     * Used to add constraints to a table. For example primary keys, unique
     * keys and foreign keys.
     *
     * @param string $name
     * @param array $attrs
     * @return $this
     */
    public function addConstraint(string $name, array $attrs): TableSchema;

    /**
     * Read information about a constraint based on name.
     *
     * @param string $name
     * @return array|null
     */
    public function getConstraint(string $name): ?array;

    /**
     * Remove a constraint.
     *
     * @param string $name
     * @return $this
     */
    public function dropConstraint(string $name): TableSchema;

    /**
     * Get the names of all the constraints in the table.
     *
     * @return string[]
     */
    public function constraints(): array;
}