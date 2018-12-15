<?php

namespace Phanda\Database\Statement;

use PDO;

class MysqlStatement extends PDOStatement
{
    /**
     * {@inheritDoc}
     *
     */
    public function execute($params = null): bool
    {
        $connection = $this->driver->getConnection();

        try {
            $connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            $result = $this->statement->execute($params);
        } finally {
            $connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }

        return $result;
    }
}