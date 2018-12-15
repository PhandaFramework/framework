<?php

namespace Phanda\Database\Driver;

use Phanda\Database\Util\Driver\Dialect\MysqlDialectTrait;

class MysqlDriver extends AbstractDriver
{
    use MysqlDialectTrait;

    /**
     * Base configuration settings for MySQL driver
     *
     * @var array
     */
    protected $_baseConfig = [
        'persistent' => true,
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => '',
        'database' => 'phanda',
        'port' => '3306',
        'flags' => [],
        'encoding' => 'utf8mb4',
        'timezone' => null,
        'init' => [],
    ];

}