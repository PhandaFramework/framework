<?php

namespace Phanda\Contracts\Database\Driver;

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

}