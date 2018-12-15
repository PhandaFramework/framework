<?php

namespace Phanda\Database\Driver;

use PDO;
use PDOException;
use Phanda\Contracts\Database\Driver\Driver as DriverContract;
use Phanda\Contracts\Database\Query\Query;
use Phanda\Contracts\Database\Statement;
use Phanda\Database\Query\QueryCompiler;
use Phanda\Database\Statement\PDOStatement;
use Phanda\Database\ValueBinder;

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
     * AbstractDriver constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->setConfiguration($config);
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
     * {@inheritDoc}
     */
    abstract public function releaseSavePointSQL($name): string;

    /**
     * {@inheritDoc}
     */
    abstract public function savePointSQL($name): string;

    /**
     * {@inheritDoc}
     */
    abstract public function rollbackSavePointSQL($name): string;

    /**
     * {@inheritDoc}
     */
    abstract public function disableForeignKeySQL(): string;

    /**
     * {@inheritDoc}
     */
    abstract public function enableForeignKeySQL(): string;

    /**
     * {@inheritDoc}
     */
    abstract public function supportsDynamicConstraints(): bool;

    /**
     * {@inheritDoc}
     */
    abstract public function queryTranslator($type): callable;

    /**
     * {@inheritDoc}
     */
    abstract public function quoteIdentifier(string $identifier): string;

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
     * @inheritdoc
     */
    public function beginTransaction(): bool
    {
        $this->connect();

        if ($this->dbConnection->inTransaction()) {
            return true;
        }

        return $this->dbConnection->beginTransaction();
    }

    /**
     * @inheritdoc
     */
    public function commitTransaction(): bool
    {
        $this->connect();

        if (!$this->dbConnection->inTransaction()) {
            return false;
        }

        return $this->dbConnection->commit();
    }

    /**
     * @inheritdoc
     */
    public function rollbackTransaction(): bool
    {
        $this->connect();

        if (!$this->dbConnection->inTransaction()) {
            return false;
        }

        return $this->dbConnection->rollBack();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsSavePoints(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function supportsQuoting(): bool
    {
        $this->connect();
        return $this->dbConnection->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'odbc';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value, $type): string
    {
        $this->connect();
        return $this->dbConnection->quote($value, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function getLastInsertId($table = null, $column = null)
    {
        $this->connect();

        if ($this->dbConnection instanceof PDO) {
            return $this->dbConnection->lastInsertId($table);
        }

        return $this->dbConnection->lastInsertId($table, $column);
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected(): bool
    {
        if ($this->dbConnection === null) {
            $connected = false;
        } else {
            try {
                $connected = $this->dbConnection->query('SELECT 1');
            } catch (PDOException $e) {
                $connected = false;
            }
        }

        return (bool)$connected;
    }

    /**
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return array
     */
    public function compileQuery(Query $query, ValueBinder $valueBinder): array
    {
        $processor = $this->newQueryCompiler();
        $translator = $this->queryTranslator($query->getType());
        $query = $translator($query);

        return [$query, $processor->compile($query, $valueBinder)];
    }

    public function newQueryCompiler(): QueryCompiler
    {
        return new QueryCompiler();
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

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->dbConnection = null;
    }

}