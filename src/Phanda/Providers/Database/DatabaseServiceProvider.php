<?php

namespace Phanda\Providers\Database;

use Phanda\Contracts\Database\Connection\Connection as ConnectionContract;
use Phanda\Contracts\Database\Driver\DriverRegistry as DriverRegistryContract;
use Phanda\Contracts\Database\Query\Query as QueryContract;
use Phanda\Contracts\Foundation\Application;
use Phanda\Database\Connection\Connection;
use Phanda\Database\Connection\Manager as ConnectionManager;
use Phanda\Contracts\Database\Connection\Manager as ConnectionManagerContract;
use Phanda\Database\Driver\DriverRegistry;
use Phanda\Database\Driver\MysqlDriver;
use Phanda\Database\Query\Query;
use Phanda\Database\Schema\SchemaCollection;
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
        $this->registerDefaultConnection();
        $this->registerSchemaCollection();
        $this->registerDatabaseQueryBuilder();
    }

    /**
     * Registers the driver registry which handles the available
     * database drivers used to connect to the database
     */
    protected function registerDriverRegistry()
    {
        $this->phanda->singleton('database.driver_registry', function ($phanda) {
            /** @var Application $phanda */
            $registry = new DriverRegistry();

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
            $configuration = config();
            $driverRegistry = $phanda->create(DriverRegistryContract::class);
            $connectionManager = new ConnectionManager($configuration, $driverRegistry);
            return $connectionManager;
        });

        $this->phanda->alias('database.connection_manager', ConnectionManagerContract::class);
        $this->phanda->alias('database.connection_manager', ConnectionManager::class);
    }

    protected function registerDefaultConnection()
    {
        $this->phanda->singleton(ConnectionContract::class, function($phanda){
            /** @var Application $phanda */
            /** @var ConnectionManagerContract $connectionManager */
            $connectionManager = $phanda->create(ConnectionManagerContract::class);

            return $connectionManager->getConnection();
        });

        $this->phanda->alias(ConnectionContract::class, Connection::class);
    }

    /**
     * Registers the SchemaCollection to ensure it's getting it from the
     * current connection
     */
    protected function registerSchemaCollection()
    {
        $this->phanda->attach(SchemaCollection::class, function($phanda) {
            /** @var Application $phanda */
            /** @var Connection $connection */
            $connection = $phanda->create(Connection::class);
            return $connection->getSchemaCollection();
        });
    }

    protected function registerDatabaseQueryBuilder()
    {
        $this->phanda->attach(QueryContract::class, function($phanda) {
            /** @var Application $phanda */
            /** @var ConnectionContract $connection */
            $connection = $phanda->create(ConnectionContract::class);
            return $connection->newQuery();
        });

        $this->phanda->alias(QueryContract::class, Query::class);
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