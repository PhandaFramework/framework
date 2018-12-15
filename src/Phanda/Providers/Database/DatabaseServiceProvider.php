<?php

namespace Phanda\Providers\Database;

use Phanda\Contracts\Database\Driver\DriverRegistry as DriverRegistryContract;
use Phanda\Contracts\Foundation\Application;
use Phanda\Database\Connection\Manager as ConnectionManager;
use Phanda\Contracts\Database\Connection\Manager as ConnectionManagerContract;
use Phanda\Database\Driver\DriverRegistry;
use Phanda\Database\Driver\MysqlDriver;
use Phanda\Providers\AbstractServiceProvider;

class DatabaseServiceProvider extends AbstractServiceProvider
{

    /**
     * Registers the classes that handle the Database connections.
     */
    public function register()
    {
        $this->registerDriverRegistry();
        $this->registerConnectionManager();
    }

    /**
     * Registers the driver registry which handles the available
     * database drivers used to connect to the database
     */
    protected function registerDriverRegistry()
    {
        $this->phanda->singleton('database.driver_registry', function ($phanda) {
            /** @var Application $phanda */
            /** @var DriverRegistry $registry */
            $registry = $phanda->create(DriverRegistry::class);

            $this->registerDrivers($registry);

            return $registry;
        });

        $this->phanda->alias('database.driver_registry', DriverRegistryContract::class);
        $this->phanda->alias('database.driver_registry', DriverRegistry::class);
    }

    /**
     * Registers the connection manager which handles the currently
     * active and inactive database connections.
     */
    protected function registerConnectionManager()
    {
        $this->phanda->singleton('database.connection_manager', function ($phanda) {
            /** @var Application $phanda */
            $connectionManager = $phanda->create(ConnectionManager::class);
            return $connectionManager;
        });

        $this->phanda->alias('database.connection_manager', ConnectionManagerContract::class);
        $this->phanda->alias('database.connection_manager', ConnectionManager::class);
    }

    /**
     * Registers all the available database drivers on the
     * database registry
     *
     * @param DriverRegistryContract $driverRegistry
     */
    protected function registerDrivers(DriverRegistryContract $driverRegistry)
    {
        $this->registerMysqlDriver($driverRegistry);
    }

    /**
     * Registers the 'mysql' database driver with the registry
     *
     * @param DriverRegistryContract $driverRegistry
     */
    protected function registerMysqlDriver(DriverRegistryContract $driverRegistry)
    {
        $driverRegistry->registerDriver('mysql', MysqlDriver::class);
    }

}