<?php

namespace Phanda\Database\Driver;

class MysqlDriver extends AbstractDriver
{
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

    /**
     * @inheritdoc
     */
    public function connect(): bool
    {
        // TODO: Implement connect() method.
    }

    /**
     * @inheritdoc
     */
    public function isEnabled(): bool
    {
        // TODO: Implement isEnabled() method.
    }

    /**
     * {@inheritDoc}
     */
    public function releaseSavePointSQL($name): string
    {
        // TODO: Implement releaseSavePointSQL() method.
    }

    /**
     * {@inheritDoc}
     */
    public function savePointSQL($name): string
    {
        // TODO: Implement savePointSQL() method.
    }

    /**
     * {@inheritDoc}
     */
    public function rollbackSavePointSQL($name): string
    {
        // TODO: Implement rollbackSavePointSQL() method.
    }

    /**
     * {@inheritDoc}
     */
    public function disableForeignKeySQL(): string
    {
        // TODO: Implement disableForeignKeySQL() method.
    }

    /**
     * {@inheritDoc}
     */
    public function enableForeignKeySQL(): string
    {
        // TODO: Implement enableForeignKeySQL() method.
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDynamicConstraints(): bool
    {
        // TODO: Implement supportsDynamicConstraints() method.
    }

    /**
     * {@inheritDoc}
     */
    public function queryTranslator($type): callable
    {
        // TODO: Implement queryTranslator() method.
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifier(string $identifier): string
    {
        // TODO: Implement quoteIdentifier() method.
    }
}