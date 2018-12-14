<?php

namespace Phanda\Contracts\Database\Connection;

interface Manager
{
    /**
     * Gets a database connection by name.
     *
     * If a database connection has not been created, it will be created
     * and then added to the resolved connections.
     *
     * @param string $name
     * @return Connection
     */
    public function getConnection($name = 'default');

    /**
     * Sets a database connection by name.
     *
     * Refer to the Phanda documentation for the formatting of the
     * configuration array that is taken as the second parameter.
     *
     * @param string $name
     * @param array $config
     * @return Manager
     */
    public function setConnection(string $name, array $config);
}