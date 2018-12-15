<?php

namespace Phanda\Contracts\Database\Driver;

interface DriverRegistry
{

    /**
     * Registers a driver by name
     *
     * @param string $name
     * @param string $driver The FQN of the driver class.
     * @return DriverRegistry
     */
    public function registerDriver(string $name, string $driver);

    /**
     * Gets a driver by name
     *
     * If a driver has not been resolved the DriverRegistry will resolve
     * the driver.
     *
     * @param string $name
     * @param array $configuration
     * @return Driver
     */
    public function getDriver(string $name, array $configuration = []);

}