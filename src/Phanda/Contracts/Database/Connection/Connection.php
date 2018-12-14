<?php

namespace Phanda\Contracts\Database\Connection;

use Phanda\Contracts\Database\Driver\Driver;
use Phanda\Exceptions\Database\Connection\ConnectionFailedException;

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

}