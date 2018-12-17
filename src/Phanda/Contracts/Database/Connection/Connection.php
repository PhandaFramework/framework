<?php

namespace Phanda\Contracts\Database\Connection;

use Phanda\Contracts\Database\Driver\Driver;
use Phanda\Contracts\Database\Query\Query;
use Phanda\Contracts\Database\Statement;
use Phanda\Database\Schema\SchemaCollection;
use Phanda\Database\ValueBinder;

interface Connection
{

    /**
     * Gets the Driver of the connection
     *
     * @return Driver
     */
    public function getDriver(): Driver;

    /**
     * Sets the driver of the connection
     *
     * @param Driver $driver
     * @return Connection
     */
    public function setDriver(Driver $driver): Connection;

    /**
     * Gets the current configuration for a given connection
     *
     * @return array
     */
    public function getConfiguration(): array;

    /**
     * Sets the configuration for the given connection
     *
     * @param array $configuration
     * @return Connection
     */
    public function setConfiguration(array $configuration): Connection;

    /**
     * Trys and connects to a database using the provided driver.
     *
     * @return bool
     */
    public function connect(): bool;

    /**
     * Disconnects from the currently connected database connection using the given driver.
     *
     * @return Connection
     */
    public function disconnect(): Connection;

    /**
     * Checks if currently connected to a database
     *
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * Prepares the given SQL into statement to be executed.
     *
     * @param $query
     * @return Statement
     */
    public function prepareQuery($query): Statement;

    /**
     * Runs the given SQL and returns the executed statement
     *
     * @param string|Query $query
     * @return Statement
     */
    public function executeQuery($query): Statement;

    /**
     * Executes SQL with not parameter bindings.
     *
     * @param string $sql
     * @return Statement
     */
    public function executeSql(string $sql);

    /**
     * Executes a snippet of SQL
     *
     * @param string $sql
     * @param array $params
     * @return Statement
     */
    public function executeSqlWithParams(string $sql, $params = []);

    /**
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function compileQuery(Query $query, ValueBinder $valueBinder): string;

    /**
     * Checks if currently performing a transaction on the database or not.
     *
     * @return bool
     */
    public function inTransaction(): bool;

    /**
     * @return \Phanda\Database\Query\Query
     */
    public function newQuery(): \Phanda\Database\Query\Query;

    /**
     * Gets a Schema\Collection object for this connection.
     *
     * @return SchemaCollection
     */
    public function getSchemaCollection(): SchemaCollection;

    /**
     * @param SchemaCollection $schemaCollection
     * @return Connection
     */
    public function setSchemaCollection(SchemaCollection $schemaCollection): Connection;

	/**
	 * Executes a callable function inside a transaction, if any exception occurs
	 * while executing the passed callable, the transaction will be rolled back
	 * If the result of the callable function is `false`, the transaction will
	 * also be rolled back. Otherwise the transaction is committed after executing
	 * the callback.
	 *
	 * The callback will receive the connection instance as its first argument.
	 *
	 * @param callable $transaction
	 * @return mixed The return value of the callback.
	 */
	public function transactional(callable $transaction);

}