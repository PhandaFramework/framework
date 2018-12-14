<?php

namespace Phanda\Database\Connection;

use Phanda\Contracts\Database\Connection\Connection;
use Phanda\Contracts\Database\Connection\Manager as ManagerContract;
use Phanda\Configuration\Repository as ConfigurationRepository;
use Phanda\Contracts\Database\Driver\DriverRegistry as DriverRegistryContract;
use Phanda\Exceptions\Database\Connection\ConnectionNotRegisteredException;

class Manager implements ManagerContract
{
    /**
     * @var ConfigurationRepository
     */
    protected $config;

    /**
     * @var array
     */
    protected $registeredConnections = [];

    /**
     * @var array
     */
    protected $resolvedConnections = [];

    /**
     * @var DriverRegistryContract
     */
    protected $driverRegistry;

    /**
     * Manager constructor.
     * @param ConfigurationRepository $config
     * @param DriverRegistryContract $driverRegistry
     */
    public function __construct(ConfigurationRepository $config, DriverRegistryContract $driverRegistry)
    {
        $this->config = $config;
        $this->driverRegistry = $driverRegistry;

        $this->loadConnectionsFromConfiguration();
    }

    /**
     * Loads the initial configurations from the configuration repository
     */
    protected function loadConnectionsFromConfiguration()
    {
        /** @var array $connections */
        $connections = $this->config->get('database.connections', []);

        foreach($connections as $name => $configuration) {
            $this->setConnection($name, $configuration);
        }
    }

    /**
     * Gets a database connection by name.
     *
     * If a database connection has not been created, it will be created
     * and then added to the resolved connections.
     *
     * @param string $name
     * @return Connection
     *
     * @throws ConnectionNotRegisteredException
     */
    public function getConnection($name = 'default')
    {
        if(isset($this->resolvedConnections[$name])) {
            return $this->resolvedConnections[$name];
        }

        if(!isset($this->registeredConnections[$name])) {
            throw new ConnectionNotRegisteredException("Connection '{$name}' has not been registered with the Connection Manager.");
        }

        return $this->resolveConnection($name);
    }

    /**
     * Resolves a connection by name.
     *
     * @param string $name
     * @return Connection
     */
    protected function resolveConnection($name = 'default')
    {
        $connection = new \Phanda\Database\Connection\Connection();

        $configuration = $this->registeredConnections[$name];
        if(!isset($configuration['driver'])) {
            throw new \LogicException("A driver must be passed as an option for a connection.");
        }

        $driver = $this->driverRegistry->getDriver($configuration['driver']);
        $connection->setDriver($driver);

        $this->resolvedConnections[$name] = $connection;
        return $connection;
    }

    /**
     * Sets a database connection by name.
     *
     * Refer to the Phanda documentation for the formatting of the
     * configuration array that is taken as the second parameter.
     *
     * @param string $name
     * @param array $config
     * @return ManagerContract
     */
    public function setConnection(string $name, array $config)
    {
        $this->registeredConnections[$name] = $config;
        return $this;
    }
}