<?php

namespace Phanda\Database\Schema;

class MysqlSchema extends AbstractSchema
{

    /**
     * Generate the SQL to list the tables.
     *
     * @param array $config
     * @return array An array of (sql, params) to execute.
     */
    public function listTablesSql(array $config): array
    {
        // TODO: Implement listTablesSql() method.
    }

    /**
     * Generate the SQL to describe a table.
     *
     * @param string $tableName The table name to get information on.
     * @param array $config
     * @return array An array of (sql, params) to execute.
     */
    public function describeColumnSql(string $tableName, array $config): array
    {
        // TODO: Implement describeColumnSql() method.
    }

    /**
     * Generate the SQL to describe the indexes in a table.
     *
     * @param string $tableName The table name to get information on.
     * @param array $config The connection configuration.
     * @return array An array of (sql, params) to execute.
     */
    public function describeIndexSql(string $tableName, array $config): array
    {
        // TODO: Implement describeIndexSql() method.
    }

    /**
     * Generate the SQL to describe the foreign keys in a table.
     *
     * @param string $tableName The table name to get information on.
     * @param array $config The connection configuration.
     * @return array An array of (sql, params) to execute.
     */
    public function describeForeignKeySql(string $tableName, array $config): array
    {
        // TODO: Implement describeForeignKeySql() method.
    }

    /**
     * Convert field description results into abstract schema fields.
     *
     * @param TableSchema $schema The table object to append fields to.
     * @param array $row The row data from `describeColumnSql`.
     * @return void
     */
    public function convertColumnDescription(TableSchema $schema, array $row)
    {
        // TODO: Implement convertColumnDescription() method.
    }

    /**
     * Convert an index description results into abstract schema indexes or constraints.
     *
     * @param TableSchema $schema
     * @param array $row The row data from `describeIndexSql`.
     * @return void
     */
    public function convertIndexDescription(TableSchema $schema, array $row)
    {
        // TODO: Implement convertIndexDescription() method.
    }

    /**
     * Convert a foreign key description into constraints on the Table object.
     *
     * @param TableSchema $schema
     * @param array $row The row data from `describeForeignKeySql`.
     * @return void
     */
    public function convertForeignKeyDescription(TableSchema $schema, array $row)
    {
        // TODO: Implement convertForeignKeyDescription() method.
    }

    /**
     * Generate the SQL to create a table.
     *
     * @param TableSchema $schema Table instance.
     * @param array $columns The columns to go inside the table.
     * @param array $constraints The constraints for the table.
     * @param array $indexes The indexes for the table.
     * @return array SQL statements to create a table.
     */
    public function createTableSql(TableSchema $schema, array $columns, array $constraints, array $indexes): array
    {
        // TODO: Implement createTableSql() method.
    }

    /**
     * Generate the SQL fragment for a single column in a table.
     *
     * @param TableSchema $schema The table instance the column is in.
     * @param string $name The name of the column.
     * @return string SQL fragment.
     */
    public function columnSql(TableSchema $schema, string $name): string
    {
        // TODO: Implement columnSql() method.
    }

    /**
     * Generate the SQL queries needed to add foreign key constraints to the table
     *
     * @param TableSchema $schema The table instance the foreign key constraints are.
     * @return array SQL fragment.
     */
    public function addConstraintSql(TableSchema $schema): array
    {
        // TODO: Implement addConstraintSql() method.
    }

    /**
     * Generate the SQL queries needed to drop foreign key constraints from the table
     *
     * @param TableSchema $schema The table instance the foreign key constraints are.
     * @return array SQL fragment.
     */
    public function dropConstraintSql(TableSchema $schema): array
    {
        // TODO: Implement dropConstraintSql() method.
    }

    /**
     * Generate the SQL fragments for defining table constraints.
     *
     * @param TableSchema $schema The table instance the column is in.
     * @param string $name The name of the column.
     * @return string SQL fragment.
     */
    public function constraintSql(TableSchema $schema, string $name): string
    {
        // TODO: Implement constraintSql() method.
    }

    /**
     * Generate the SQL fragment for a single index in a table.
     *
     * @param TableSchema $schema The table object the column is in.
     * @param string $name The name of the column.
     * @return string SQL fragment.
     */
    public function indexSql(TableSchema $schema, string $name): string
    {
        // TODO: Implement indexSql() method.
    }

    /**
     * Generate the SQL to truncate a table.
     *
     * @param TableSchema $schema Table instance.
     * @return array SQL statements to truncate a table.
     */
    public function truncateTableSql(TableSchema $schema): array
    {
        // TODO: Implement truncateTableSql() method.
    }
}