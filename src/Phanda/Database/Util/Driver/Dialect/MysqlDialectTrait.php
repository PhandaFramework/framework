<?php

namespace Phanda\Database\Util\Driver\Dialect;

trait MysqlDialectTrait
{
    use SqlDialectTrait;

    /**
     * String used to start a database identifier quoting to make it safe
     *
     * @var string
     */
    protected $startQuote = '`';

    /**
     * String used to end a database identifier quoting to make it safe
     *
     * @var string
     */
    protected $endQuote = '`';

    /**
     * {@inheritDoc}
     */
    public function disableForeignKeySQL(): string
    {
        return 'SET foreign_key_checks = 0';
    }

    /**
     * {@inheritDoc}
     */
    public function enableForeignKeySQL(): string
    {
        return 'SET foreign_key_checks = 1';
    }
}