<?php

namespace Phanda\Database\Driver;

use PDO;
use Phanda\Contracts\Database\Driver\Driver as DriverContract;
use Phanda\Contracts\Database\Query\Query;
use Phanda\Contracts\Database\Statement;

abstract class AbstractDriver implements DriverContract
{

    /**
     * @var PDO
     */
    protected $dbConnection;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * Base configuration provided by the driver that gets
     * merged with the user configuration
     *
     * @var array
     */
    protected $baseConfig = [];

    /**
     * Whether or not the driver is automatically quoting identifiers
     *
     * @var bool
     */
    protected $automaticIdentifierQuoting = false;

    public function __construct(array $config = [])
    {
        $this->setConfiguration($config);

        if($this->getConfiguration()['quoteIdentifiers']) {
            $this->enableAutoQuoting();
        }
    }

    /**
     * @inheritdoc
     */
    abstract public function connect(): bool;

    /**
     * @inheritdoc
     */
    abstract public function isEnabled(): bool;

    /**
     * @param array $config
     * @return DriverContract
     */
    public function setConfiguration(array $config): DriverContract
    {
        $config += $this->baseConfig;
        $this->config = $config;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->config;
    }

    /**
     * Sets the auto quoting of identifiers in queries.
     *
     * @param bool $enable
     * @return $this
     */
    public function enableAutoQuoting(bool $enable = true): self
    {
        $this->automaticIdentifierQuoting = $enable;
        return $this;
    }

    /**
     * Disable auto quoting of identifiers in queries.
     *
     * @return $this
     */
    public function disableAutoQuoting(): self
    {
        $this->automaticIdentifierQuoting = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoQuotingEnabled(): bool
    {
        return $this->automaticIdentifierQuoting;
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->dbConnection;
    }

    /**
     * @param PDO $dbConnection
     * @return AbstractDriver
     */
    public function setConnection(PDO $dbConnection): AbstractDriver
    {
        $this->dbConnection = $dbConnection;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function disconnect(): DriverContract
    {
        $this->dbConnection = null;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function prepare($query): Statement
    {
        $this->connect();
        $isObject = $query instanceof Query;
        $statement = $this->dbConnection->prepare($isObject ? $query->toSql() : $query);

        return new PDOStatement($statement, $this);
    }

    /**
     * Handles the creation of the internal PDO connection.
     *
     * @param string $dsn
     * @param array $config
     * @return bool
     */
    protected function handlePDOConnection(string $dsn, array $config): bool
    {
        $connection = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            $config['flags']
        );

        $this->setConnection($connection);

        return true;
    }

}