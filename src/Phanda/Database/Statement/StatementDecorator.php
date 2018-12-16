<?php

namespace Phanda\Database\Statement;

use Phanda\Contracts\Database\Driver\Driver;
use Phanda\Contracts\Database\Statement as StatementContract;
use Traversable;

class StatementDecorator implements StatementContract, \Countable, \IteratorAggregate
{
    /**
     * @var StatementContract|\PDOStatement
     */
    protected $statement;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var bool
     */
    protected $executed = false;

    /**
     * StatementDecorator constructor.
     *
     * @param null|StatementContract|\PDOStatement $statement
     * @param null|Driver $driver
     */
    public function __construct($statement = null, $driver = null)
    {
        $this->statement = $statement;
        $this->driver = $driver;
    }

    /**
     * Retrieve an external iterator
     *
     * @return Traversable
     */
    public function getIterator()
    {
        if(!$this->executed) {
            $this->execute();
        }

        return $this->statement;
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count(): int
    {
        return $this->statement->count();
    }

    /**
     * Executes the given statement.
     *
     * @return bool
     */
    public function execute(): bool
    {
        $this->executed = true;
        return $this->statement->execute();
    }

    /**
     * Binds a value to the given statement
     *
     * @param string|int $column name or param position to be bound
     * @param mixed $value The value to bind to variable in query
     * @return StatementContract
     */
    public function bindValue($column, $value): StatementContract
    {
        return $this->statement->bindValue($column, $value);
    }

    /**
     * @inheritdoc
     */
    public function bindParams(array $params)
    {
        if (empty($params)) {
            return;
        }

        $anonymousParams = is_int(key($params)) ? true : false;
        $offset = 1;

        foreach ($params as $index => $value) {
            if ($anonymousParams) {
                $index += $offset;
            }

            $this->bindValue($index, $value);
        }
    }

    /**
     * Closes the current cursor on the database.
     *
     * You should not have to call this as it is called automatically
     * internally. Used to optimise calls to database by cleaning
     * current queries, etc.
     *
     * @return StatementContract
     */
    public function closeCursor(): StatementContract
    {
        return $this->statement->closeCursor();
    }

    /**
     * Gets the count of columns in this statement
     *
     * @return int
     */
    public function getColumnCount(): int
    {
        return $this->statement->getColumnCount();
    }

    /**
     * Gets the count of rows in this statement
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->statement->getRowCount();
    }

    /**
     * Gets the latest primary key that's been inserted
     *
     * @param null|string $table
     * @param null|string $column
     * @return string
     */
    public function getLastInsertId($table = null, $column = null): string
    {
        $row = null;
        if ($column && $this->getColumnCount()) {
            $row = $this->fetch(static::FETCH_TYPE_ASSOC);
        }
        if (isset($row[$column])) {
            return $row[$column];
        }

        return $this->driver->getLastInsertId($table, $column);
    }

    /**
     * Gets the last error code that occurred during execution of this
     * statement.
     *
     * @return string|int
     */
    public function getLastErrorCode()
    {
        return $this->statement->getLastErrorCode();
    }

    /**
     * Gets the last error and the information associated with it
     * during the execution of this statement
     *
     * @return array
     */
    public function getLastErrorInfo()
    {
        return $this->statement->getLastErrorInfo();
    }

    /**
     * Gets the next row after executing this statement
     *
     * @param string $type
     * @return array|bool
     */
    public function fetch($type = self::FETCH_TYPE_ASSOC)
    {
        return $this->statement->fetch($type);
    }

    /**
     * Gets all the rows returned by executing this statement
     *
     * @param string $type
     * @return array
     */
    public function fetchAll($type = self::FETCH_TYPE_ASSOC): array
    {
        return $this->statement->fetchAll($type);
    }

    /**
     * Gets the statement decorated by this class
     *
     * @return \PDOStatement|StatementContract|null
     */
    public function getDecoratedStatement()
    {
        return $this->statement;
    }
}