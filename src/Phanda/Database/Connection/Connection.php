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
use Phanda\Exceptions\Database\Connection\TransactionFailedException;
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
	 * Contains how many nested transactions have been started.
	 *
	 * @var int
	 */
	protected $transactionLevel = 0;

	/**
	 * Whether a transaction is active in this connection.
	 *
	 * @var bool
	 */
	protected $transactionStarted = false;

	/**
	 * @var bool
	 */
	protected $useSavePoints = false;

	protected $failedTransactionException;

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
     * Executes SQL with not parameter bindings.
     *
     * @param string $sql
     * @return Statement
     *
     * @throws Exception
     */
    public function executeSql(string $sql)
    {
        return $this->getRetryConnectionCommand()->run(function() use ($sql) {
            $statement = $this->prepareQuery($sql);
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
    public function executeSqlWithParams(string $sql, $params = [])
    {
        return $this->getRetryConnectionCommand()->run(function() use($sql, $params) {
            if(!empty($params)) {
                $statement = $this->prepareQuery($sql);
                $statement->bindParams($params);
                $statement->execute();
            } else {
                $statement = $this->executeSql($sql);
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
		return $this->transactionStarted;
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

	/**
	 * Executes a callable function inside a transaction, if any exception occurs
	 * while executing the passed callable, the transaction will be rolled back
	 * If the result of the callable function is `false`, the transaction will
	 * also be rolled back. Otherwise the transaction is committed after executing
	 * the callback.
	 *
	 * The callback will receive the connection instance as its first argument.
	 *
	 * @param callable $transaction
	 * @return mixed The return value of the callback.
	 *
	 * @throws \Exception
	 */
	public function transactional(callable $transaction)
	{
		$this->begin();

		try {
			$result = $transaction($this);
		} catch (Exception $e) {
			$this->rollback(false);
			throw $e;
		}

		if ($result === false) {
			$this->rollback(false);

			return false;
		}

		try {
			$this->commit();
		} catch (Exception $e) {
			$this->rollback(false);
			throw $e;
		}

		return $result;
	}

	/**
	 * Starts a transaction.
	 *
	 * @throws Exception
	 */
	public function begin()
	{
		if (!$this->transactionStarted) {

			$this->getRetryConnectionCommand()->run(function () {
				$this->driver->beginTransaction();
			});

			$this->transactionLevel = 0;
			$this->transactionStarted = true;
			$this->failedTransactionException = null;

			return;
		}

		$this->transactionLevel++;
		if ($this->isSavePointsEnabled()) {
			$this->createSavePoint((string)$this->transactionLevel);
		}
	}

	/**
	 * Rolls back the current transaction
	 *
	 * @param bool|null $toBeginning
	 * @return bool
	 * @throws Exception
	 */
	public function rollback(?bool $toBeginning = null)
	{
		if (!$this->transactionStarted) {
			return false;
		}

		$useSavePoint = $this->isSavePointsEnabled();
		if ($toBeginning === null) {
			$toBeginning = !$useSavePoint;
		}
		if ($this->transactionLevel === 0 || $toBeginning) {
			$this->transactionLevel = 0;
			$this->transactionStarted = false;
			$this->failedTransactionException = null;
			$this->driver->rollbackTransaction();

			return true;
		}

		$savePoint = $this->transactionLevel--;
		if ($useSavePoint) {
			$this->rollbackSavepoint($savePoint);
		} elseif ($this->failedTransactionException === null) {
			$this->failedTransactionException = new TransactionFailedException("Cannot commit transaction - rollback() has been already called in the nested transaction");
		}

		return true;
	}

	/**
	 * Commits current transaction.
	 *
	 * @return bool true on success, false otherwise
	 * @throws Exception
	 */
	public function commit()
	{
		if (!$this->transactionStarted) {
			return false;
		}

		if ($this->transactionLevel === 0) {
			if ($this->wasNestedTransactionRolledback()) {
				$e = $this->failedTransactionException;
				$this->failedTransactionException = null;
				throw $e;
			}

			$this->transactionStarted = false;
			$this->failedTransactionException = null;

			return $this->driver->commitTransaction();
		}
		if ($this->isSavePointsEnabled()) {
			$this->releaseSavePoint((string)$this->transactionLevel);
		}

		$this->transactionLevel--;

		return true;
	}

	/**
	 * Creates a new save point for nested transactions.
	 *
	 * @param string $name The save point name.
	 * @return void
	 *
	 * @throws Exception
	 */
	public function createSavePoint($name)
	{
		$this->executeSql($this->driver->savePointSQL($name))->closeCursor();
	}

	/**
	 * Rolls back to a savepoint
	 *
	 * @param $name
	 * @throws Exception
	 */
	public function rollbackSavepoint($name)
	{
		$this->executeSql($this->driver->rollbackSavePointSQL($name))->closeCursor();
	}

	/**
	 * Releases a savepoint
	 *
	 * @param $name
	 * @throws Exception
	 */
	public function releaseSavepoint($name)
	{
		$this->executeSql($this->driver->releaseSavePointSQL($name))->closeCursor();
	}

	public function enableSavePoints($enable)
	{
		if ($enable === false) {
			$this->useSavePoints = false;
		} else {
			$this->useSavePoints = $this->driver->supportsSavePoints();
		}

		return $this;
	}

	/**
	 * Disables the usage of savepoints.
	 *
	 * @return $this
	 */
	public function disableSavePoints()
	{
		$this->useSavePoints = false;

		return $this;
	}

	/**
	 * Returns whether this connection is using savepoints for nested transactions
	 *
	 * @return bool true if enabled, false otherwise
	 */
	public function isSavePointsEnabled()
	{
		return $this->useSavePoints;
	}

	/**
	 * @return bool
	 */
	protected function wasNestedTransactionRolledback(): bool
	{
		return $this->failedTransactionException instanceof TransactionFailedException;
	}
}