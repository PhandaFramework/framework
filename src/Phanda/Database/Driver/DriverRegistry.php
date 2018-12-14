<?php

namespace Phanda\Database\Driver;

use Phanda\Contracts\Database\Driver\Driver as DriverContract;
use Phanda\Contracts\Database\Driver\DriverRegistry as DriverRegistryContract;
use Phanda\Exceptions\Database\Driver\DriverNotRegisteredException;

class DriverRegistry implements DriverRegistryContract
{
    /**
     * @var array
     */
    protected $drivers = [];

    /**
     * @var array
     */
    protected $resolvedDrivers = [];

    /**
     * Registers a driver by name
     *
     * @param string $name
     * @param $driver
     * @return DriverRegistryContract
     */
    public function registerDriver(string $name, string $driver)
    {
        $this->drivers[$name] = $driver;
        return $this;
    }

    /**
     * Gets a driver by name
     *
     * @param string $name
     * @return DriverContract
     *
     * @throws DriverNotRegisteredException
     */
    public function getDriver(string $name)
    {
        if(isset($this->resolvedDrivers[$name])) {
            return $this->resolvedDrivers[$name];
        }

        if(!isset($this->drivers[$name])) {
            throw new DriverNotRegisteredException("The driver '{$name}' has not been registered with the DriverRegistry.");
        }

        $driver = phanda()->create($this->drivers[$name]);

        if(!$driver instanceof DriverContract) {
            throw new \LogicException("The driver '{$name}' must be an instance of Phanda\\Contracts\\Database\\Driver\\Driver.");
        }

        $this->resolvedDrivers[$name] = $driver;

        return $driver;
    }
}