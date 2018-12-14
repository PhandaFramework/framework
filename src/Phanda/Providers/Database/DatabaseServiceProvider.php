<?php

namespace Phanda\Providers\Database;

use Phanda\Contracts\Database\Driver\DriverRegistry as DriverRegistryContract;
use Phanda\Contracts\Foundation\Application;
use Phanda\Database\Connection\Manager as ConnectionManager;
use Phanda\Contracts\Database\Connection\Manager as ConnectionManagerContract;
use Phanda\Database\Driver\DriverRegistry;
use Phanda\Providers\AbstractServiceProvider;

class DatabaseServiceProvider extends AbstractServiceProvider
{

    public function register()
    {
        $this->registerDriverRegistry();
        $this->registerConnectionManager();
    }

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

    protected function registerDrivers(DriverRegistryContract $driverRegistry)
    {
        $this->registerMysqlDriver($driverRegistry);
    }

    protected function registerMysqlDriver(DriverRegistryContract $driverRegistry)
    {

    }

}