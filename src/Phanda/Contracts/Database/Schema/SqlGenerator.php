<?php

namespace Phanda\Contracts\Database\Schema;

use Phanda\Contracts\Database\Connection\Connection;

interface SqlGenerator
{
    /**
     * Generate the SQL to create the table
     *
     * @param Connection $connection
     * @return array
     */
    public function createSql(Connection $connection): array;

    /**
     * Generate the SQL to drop a table
     *
     * @param Connection $connection
     * @return array
     */
    public function dropSql(Connection $connection): array;

    /**
     * Generate the SQL truncate a table
     *
     * @param Connection $connection
     * @return array
     */
    public function truncateSql(Connection $connection): array;

    /**
     * Generate the SQL to add a constraint to the table
     *
     * @param Connection $connection
     * @return array
     */
    public function addConstraintSql(Connection $connection): array;

    /**
     * Generate the SQL to drop a constraint on the table
     *
     * @param Connection $connection
     * @return array
     */
    public function dropConstraintSql(Connection $connection): array;
}