<?php

namespace Phanda\Database\Statement;

use \PDO;
use \PDOStatement as Statement;
use Phanda\Contracts\Database\Driver\Driver;
use Phanda\Contracts\Database\Statement as StatementContract;

class PDOStatement extends StatementDecorator
{
    /**
     * PDOStatement constructor.
     *
     * @param Statement|null $statement
     * @param null|Driver $driver
     */
    public function __construct(Statement $statement = null, $driver = null)
    {
        parent::__construct($statement, $driver);
    }

    /**
     * @inheritdoc
     */
    public function bindValue($column, $value): StatementContract
    {
        $this->statement->bindValue($column, $value);
    }

    /**
     * @inheritdoc
     */
    public function fetch($type = parent::FETCH_TYPE_ASSOC)
    {
        if ($type === static::FETCH_TYPE_NUMERIC) {
            return $this->statement->fetch(PDO::FETCH_NUM);
        }

        if ($type === static::FETCH_TYPE_ASSOC) {
            return $this->statement->fetch(PDO::FETCH_ASSOC);
        }

        if ($type === static::FETCH_TYPE_OBJ) {
            return $this->statement->fetch(PDO::FETCH_OBJ);
        }

        return $this->statement->fetch($type);
    }

    /**
     * @inheritdoc
     */
    public function fetchAll($type = parent::FETCH_TYPE_ASSOC): array
    {
        if ($type === static::FETCH_TYPE_NUMERIC) {
            return $this->statement->fetchAll(PDO::FETCH_NUM);
        }

        if ($type === static::FETCH_TYPE_ASSOC) {
            return $this->statement->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($type === static::FETCH_TYPE_OBJ) {
            return $this->statement->fetchAll(PDO::FETCH_OBJ);
        }

        return $this->statement->fetchAll($type);
    }

}