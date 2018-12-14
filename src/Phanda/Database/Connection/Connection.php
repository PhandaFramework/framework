<?php

namespace Phanda\Database\Connection;

use Phanda\Contracts\Database\Connection\Connection as ConnectionContact;
use Phanda\Contracts\Database\Driver\Driver;

class Connection implements ConnectionContact
{

    /**
     * @var Driver
     */
    protected $driver;

    public function __construct()
    {

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
        return $this;
    }
}