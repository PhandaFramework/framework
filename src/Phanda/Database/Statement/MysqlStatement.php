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
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $result = $this->statement->execute($params);
        } finally {
            $connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }

        $this->executed = true;
        return $result;
    }
}