<?php

namespace Phanda\Database\Statement;

use Phanda\Contracts\Database\Driver\Driver;
use Phanda\Contracts\Database\Statement as StatementContract;

class CallbackStatement extends StatementDecorator
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * CallbackStatement constructor.
     *
     * @param callable $callback
     * @param StatementContract $statement
     * @param Driver|null $driver
     */
    public function __construct(StatementContract $statement, ?Driver $driver, callable $callback)
    {
        parent::__construct($statement, $driver);
        $this->callback = $callback;
    }

    /**
     * @param string $type
     * @return array|bool
     */
    public function fetch($type = parent::FETCH_TYPE_ASSOC)
    {
        $callback = $this->callback;
        $row = $this->statement->fetch($type);

        return $row === false ? $row : $callback($row);
    }

    /**
     * @param string $type
     * @return array
     */
    public function fetchAll($type = parent::FETCH_TYPE_ASSOC): array
    {
        return array_map($this->callback, $this->statement->fetchAll($type));
    }

}