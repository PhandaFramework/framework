<?php

namespace Phanda\Contracts\Database\Driver;

use PDO;
use Phanda\Contracts\Database\Query\Query;
use Phanda\Contracts\Database\Statement;
use Phanda\Database\Query\QueryCompiler;
use Phanda\Database\ValueBinder;

interface Driver
{

    /**
     * Gets the current configuration for a given driver.
     *
     * @return array
     */
    public function getConfiguration(): array;

    /**
     * Sets the current configuration for a given driver.
     *
     * @param array $configuration
     * @return Driver
     */
    public function setConfiguration(array $configuration): Driver;

    /**
     * Attempts to connect to a database using the provided configuration
     *
     * @return bool
     */
    public function connect(): bool;

    /**
     * Disconnects from the currently connected database.
     *
     * @return Driver
     */
    public function disconnect(): Driver;

    /**
     * Checks if currently connected to a database
     *
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * Checks if PHP can use this driver to connect to the database
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Prepares the given SQL into statement to be executed.
     *
     * @param string|Query $query
     * @return Statement
     */
    public function prepare($query): Statement;

    /**
     * Returns last id generated for a table or sequence in database.
     *
     * @param string|null $table
     * @param string|null $column
     * @return string|int
     */
    public function getLastInsertId($table = null, $column = null);

    /**
     * Sets the auto quoting of identifiers in queries.
     *
     * @param bool $enable
     * @return $this
     */
    public function enableAutoQuoting(bool $enable = true);

    /**
     * Disable auto quoting of identifiers in queries.
     *
     * @return $this
     */
    public function disableAutoQuoting();

    /**
     * @return bool
     */
    public function isAutoQuotingEnabled(): bool;

    /**
     * @return PDO
     */
    public function getConnection(): PDO;

    /**
     * @param PDO $dbConnection
     * @return self
     */
    public function setConnection(PDO $dbConnection);

    /**
     * Begins a transaction
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * Commits a transaction.
     *
     * @return bool
     */
    public function commitTransaction(): bool;

    /**
     * Rollbacks a transaction.
     *
     * @return bool
     */
    public function rollbackTransaction(): bool;

    /**
     * Get the SQL for releasing a save point.
     *
     * @param string $name
     * @return string
     */
    public function releaseSavePointSQL($name): string;

    /**
     * Get the SQL for creating a save point.
     *
     * @param string $name
     * @return string
     */
    public function savePointSQL($name): string;

    /**
     * Get the SQL for rolling back a save point.
     *
     * @param string $name
     * @return string
     */
    public function rollbackSavePointSQL($name): string;

    /**
     * Get the SQL for disabling foreign keys.
     *
     * @return string
     */
    public function disableForeignKeySQL(): string;

    /**
     * Get the SQL for enabling foreign keys.
     *
     * @return string
     */
    public function enableForeignKeySQL(): string;

    /**
     * Returns whether the driver supports adding or dropping constraints
     * to already created tables.
     *
     * @return bool
     */
    public function supportsDynamicConstraints(): bool;

    /**
     * Checks if this driver supports save points for nested transactions.
     *
     * @return bool
     */
    public function supportsSavePoints(): bool;

    /**
     * Checks if the driver supports quoting.
     *
     * @return bool
     */
    public function supportsQuoting(): bool;

    /**
     * Quotes a database identifier (a column name, table name, etc..) to
     * be used safely in queries without the risk of using reserved words.
     *
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier(string $identifier): string;

    /**
     * Returns a value in a safe representation to be used in a query string
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    public function quoteValue($value, $type): string;

    /**
     * Returns a callable function that will be used to transform a passed Query object.
     *
     * @param string $type
     * @return callable
     */
    public function queryTranslator($type): callable;

    /**
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return array
     */
    public function compileQuery(Query $query, ValueBinder $valueBinder): array;

    /**
     * Gets a new QueryCompiler instance to compile queries
     *
     * @return QueryCompiler
     */
    public function newQueryCompiler(): QueryCompiler;
}