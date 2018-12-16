<?php

namespace Phanda\Database\Connection;

use Exception;
use Phanda\Contracts\Database\Connection\Connection as ConnectionContact;
use Phanda\Contracts\Database\Driver\Driver;
use Phanda\Contracts\Database\Query\Query as QueryContract;
use Phanda\Contracts\Database\Statement;
use Phanda\Database\Query\Query;
use Phanda\Database\Schema\SchemaCollection;
use Phanda\Database\ValueBinder;
use Phanda\Exceptions\Database\Connection\ConnectionFailedException;
use Phanda\Support\RetryCommand;

class Connection implements ConnectionContact
{
    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var string
     */
    private $name;

    /**
     * @var SchemaCollection
     */
    protected $schemaCollection;

    /**
     * Connection constructor.
     * @param string $name
     * @param array $configuration
     */
    public function __construct(string $name, array $configuration)
    {
        $this->configuration = $configuration;
        $this->name = $name;
    }

    /**
     * Gets the retry connection command used to try connections again incase of disconnection
     *
     * @return RetryCommand
     */
    protected function getRetryConnectionCommand()
    {
        return new RetryCommand(new ReconnectCommandStrategy($this));
    }

    /**
     * Gets the Driver of the connection
     *
     * @return Driver
     */
    public function getDriver(): Driver
    {
        return $this->driver;
    }

    /**
     * Sets the driver of the connection
     *
     * @param Driver $driver
     * @return ConnectionContact
     */
    public function setDriver(Driver $driver): ConnectionContact
    {
        $this->driver = $driver;
        $this->driver->setConfiguration($this->getConfiguration());

        return $this;
    }

    /**
     * Gets the current configuration for a given connection
     *
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * Sets the configuration for the given connection
     *
     * @param array $configuration
     * @return ConnectionContact
     */
    public function setConfiguration(array $configuration): ConnectionContact
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * Trys and connects to a database using the provided driver.
     *
     * @return bool
     *
     * @throws ConnectionFailedException
     */
    public function connect(): bool
    {
        try {
            return $this->driver->connect();
        } catch (Exception $e) {
            throw new ConnectionFailedException('Connection to database failed: ' . $e->getMessage());
        }
    }

    /**
     * Disconnects from the currently connected database connection using the given driver.
     *
     * @return ConnectionContact
     */
    public function disconnect(): ConnectionContact
    {
        $this->driver->disconnect();
        return $this;
    }

    /**
     * Checks if currently connected to a database
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->driver->isConnected();
    }

    /**
     * Prepares the given query into statement to be executed.
     *
     * @param string|QueryContract $query
     * @return Statement
     *
     * @throws Exception
     */
    public function prepareQuery($query): Statement
    {
        return $this->getRetryConnectionCommand()->run(function() use ($query) {
           return $this->driver->prepare($query);
        });
    }

    /**
     * Runs the given query and returns the executed statement
     *
     * @param string|QueryContract $query
     * @return Statement
     *
     * @throws Exception
     */
    public function executeQuery($query): Statement
    {
        return $this->getRetryConnectionCommand()->run(function() use ($query) {
           $statement = $this->prepareQuery($query);
           $query->getValueBinder()->attachToStatement($statement);
           $statement->execute();
           return $statement;
        });
    }

    /**
     * Executes a snippet of SQL
     *
     * @param string $sql
     * @param array $params
     * @return Statement
     *
     * @throws Exception
     */
    public function executeSql(string $sql, $params = [])
    {
        return $this->getRetryConnectionCommand()->run(function() use($sql, $params) {
            if(!empty($params)) {
                $statement = $this->prepareQuery($sql);
                $statement->bindParams($params);
                $statement->execute();
            } else {
                $statement = $this->executeQuery($sql);
            }

            return $statement;
        });
    }

    /**
     * @param QueryContract $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function compileQuery(QueryContract $query, ValueBinder $valueBinder): string
    {
        return $this->getDriver()->compileQuery($query, $valueBinder)[1];
    }

    /**
     * Checks if currently performing a transaction on the database or not.
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        // TODO: Implement inTransaction() method.
    }

    public function newQuery(): Query
    {
        return new Query($this);
    }

    /**
     * Gets a Schema\Collection object for this connection.
     *
     * @return SchemaCollection
     */
    public function getSchemaCollection(): SchemaCollection
    {
        if ($this->schemaCollection !== null) {
            return $this->schemaCollection;
        }

        return $this->schemaCollection = new SchemaCollection($this);
    }

    /**
     * @param SchemaCollection $schemaCollection
     * @return Connection
     */
    public function setSchemaCollection(SchemaCollection $schemaCollection): ConnectionContact
    {
        $this->schemaCollection = $schemaCollection;
        return $this;
    }
}