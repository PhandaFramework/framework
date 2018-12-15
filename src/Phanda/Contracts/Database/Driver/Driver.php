<?php

namespace Phanda\Contracts\Database\Driver;

use PDO;
use Phanda\Contracts\Database\Query\Query;
use Phanda\Contracts\Database\Statement;
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
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function compileQuery(Query $query, ValueBinder $valueBinder): string;

    /**
     * Quotes a database identifier (a column name, table name, etc..) to
     * be used safely in queries without the risk of using reserved words.
     *
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier(string $identifier): string;

    /**
     * Sets the auto quoting of identifiers in queries.
     *
     * @param bool $enable
     * @return $this
     */
    public function enableAutoQuoting(bool $enable = true): self;

    /**
     * Disable auto quoting of identifiers in queries.
     *
     * @return $this
     */
    public function disableAutoQuoting(): self;

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
    public function setConnection(PDO $dbConnection): self;
}