<?php

namespace Phanda\Database\Driver;

use PDO;
use Phanda\Contracts\Database\Query\Query;
use Phanda\Contracts\Database\Statement as StatementContract;
use Phanda\Database\Statement\MysqlStatement;
use Phanda\Database\Util\Driver\Dialect\MysqlDialectTrait;

class MysqlDriver extends AbstractDriver
{
    use MysqlDialectTrait;

    /**
     * Base configuration settings for MySQL driver
     *
     * @var array
     */
    protected $baseConfig = [
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

    /**
     * The server version
     *
     * @var string
     */
    protected $version;

    /**
     * Whether or not the server supports native JSON
     *
     * @var bool
     */
    protected $supportsNativeJson;

    /**
     * @inheritdoc
     */
    public function connect(): bool
    {
        if ($this->dbConnection) {
            return true;
        }

        $config = $this->getConfiguration();

        if ($config['timezone'] === 'UTC') {
            $config['timezone'] = '+0:00';
        }

        if (!empty($config['timezone'])) {
            $config['init'][] = sprintf("SET time_zone = '%s'", $config['timezone']);
        }

        if (!empty($config['encoding'])) {
            $config['init'][] = sprintf('SET NAMES %s', $config['encoding']);
        }

        $config['flags'] += [
            PDO::ATTR_PERSISTENT => $config['persistent'],
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];

        if (!empty($config['ssl_key']) && !empty($config['ssl_cert'])) {
            $config['flags'][PDO::MYSQL_ATTR_SSL_KEY] = $config['ssl_key'];
            $config['flags'][PDO::MYSQL_ATTR_SSL_CERT] = $config['ssl_cert'];
        }

        if (!empty($config['ssl_ca'])) {
            $config['flags'][PDO::MYSQL_ATTR_SSL_CA] = $config['ssl_ca'];
        }

        if (empty($config['unix_socket'])) {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['encoding']}";
        } else {
            $dsn = "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}";
        }

        $this->handlePDOConnection($dsn, $config);

        if (!empty($config['init'])) {
            $connection = $this->getConnection();

            foreach ((array)$config['init'] as $command) {
                $connection->exec($command);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function prepare($query): StatementContract
    {
        $this->connect();
        $isObject = $query instanceof Query;
        $statement = $this->dbConnection->prepare($isObject ? $query->toSql() : $query);
        $result = new MysqlStatement($statement, $this);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled(): bool
    {
        return in_array('mysql', PDO::getAvailableDrivers());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDynamicConstraints(): bool
    {
        return true;
    }

    /**
     * Returns true if the server supports native JSON columns
     *
     * @return bool
     */
    public function supportsNativeJson(): bool
    {
        if ($this->supportsNativeJson !== null) {
            return $this->supportsNativeJson;
        }

        if ($this->version === null) {
            $this->version = $this->dbConnection->getAttribute(PDO::ATTR_SERVER_VERSION);
        }

        return $this->supportsNativeJson = version_compare($this->version, '5.7.0', '>=');
    }
}