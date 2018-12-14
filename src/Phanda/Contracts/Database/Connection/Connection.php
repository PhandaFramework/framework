<?php

namespace Phanda\Contracts\Database\Connection;

use Phanda\Contracts\Database\Driver\Driver;

interface Connection
{

    /**
     * Gets the Driver of the connection
     *
     * @return Driver
     */
    public function getDriver(): Driver;

    /**
     * Sets the driver of the connection
     *
     * @param Driver $driver
     * @return Connection
     */
    public function setDriver(Driver $driver): Connection;

}