<?php

namespace Phanda\Database\Schema;

use Phanda\Contracts\Database\Driver\Driver;

abstract class AbstractSchema
{
    /**
     * @var Driver
     */
    protected $driver;

    public function __construct(Driver $driver)
    {
        $driver->connect();
        $this->driver = $driver;
    }

    /**
     * Generate an ON clause for a foreign key.
     *
     * @param string|null $on
     * @return string
     */
    protected function foreignOnClause(?string $on): string
    {
        if ($on === TableSchema::ACTION_SET_NULL) {
            return 'SET NULL';
        }
        if ($on === TableSchema::ACTION_SET_DEFAULT) {
            return 'SET DEFAULT';
        }
        if ($on === TableSchema::ACTION_CASCADE) {
            return 'CASCADE';
        }
        if ($on === TableSchema::ACTION_RESTRICT) {
            return 'RESTRICT';
        }
        if ($on === TableSchema::ACTION_NO_ACTION) {
            return 'NO ACTION';
        }
    }

    /**
     * Convert string on clauses to the abstract ones.
     *
     * @param string $clause
     * @return string|null
     */
    protected function convertOnClause(string $clause): ?string
    {
        if ($clause === 'CASCADE' || $clause === 'RESTRICT') {
            return strtolower($clause);
        }
        if ($clause === 'NO ACTION') {
            return TableSchema::ACTION_NO_ACTION;
        }

        return TableSchema::ACTION_SET_NULL;
    }

    /**
     * Convert foreign key constraints references to a valid
     * stringified list
     *
     * @param string|array $references
     * @return string
     */
    protected function convertConstraintColumns($references): string
    {
        if (is_string($references)) {
            return $this->driver->quoteIdentifier($references);
        }

        return implode(', ', array_map(
            [$this->driver, 'quoteIdentifier'],
            $references
        ));
    }

    /**
     * Generate the SQL to drop a table.
     *
     * @param TableSchema $schema
     * @return array
     */
    public function dropTableSql(TableSchema $schema): array
    {
        $sql = sprintf(
            'DROP TABLE %s',
            $this->driver->quoteIdentifier($schema->name())
        );

        return [$sql];
    }

    /**
     * Generate the SQL to list the tables.
     *
     * @param array $config
     * @return array An array of (sql, params) to execute.
     */
    abstract public function listTablesSql(array $config): array;

    /**
     * Generate the SQL to describe a table.
     *
     * @param string $tableName The table name to get information on.
     * @param array $config
     * @return array An array of (sql, params) to execute.
     */
    abstract public function describeColumnSql(string $tableName, array $config): array;

    /**
     * Generate the SQL to describe the indexes in a table.
     *
     * @param string $tableName The table name to get information on.
     * @param array $config The connection configuration.
     * @return array An array of (sql, params) to execute.
     */
    abstract public function describeIndexSql(string $tableName, array $config): array;

    /**
     * Generate the SQL to describe the foreign keys in a table.
     *
     * @param string $tableName The table name to get information on.
     * @param array $config The connection configuration.
     * @return array An array of (sql, params) to execute.
     */
    abstract public function describeForeignKeySql(string $tableName, array $config): array;

    /**
     * Generate the SQL to describe table options
     *
     * @param string $tableName Table name.
     * @param array $config The connection configuration.
     * @return array SQL statements to get options for a table.
     */
    public function describeOptionsSql(string $tableName, array $config): array
    {
        return ['', ''];
    }

    /**
     * Convert field description results into abstract schema fields.
     *
     * @param TableSchema $schema The table object to append fields to.
     * @param array $row The row data from `describeColumnSql`.
     * @return void
     */
    abstract public function convertColumnDescription(TableSchema $schema, array $row);

    /**
     * Convert an index description results into abstract schema indexes or constraints.
     *
     * @param TableSchema $schema
     * @param array $row The row data from `describeIndexSql`.
     * @return void
     */
    abstract public function convertIndexDescription(TableSchema $schema, array $row);

    /**
     * Convert a foreign key description into constraints on the Table object.
     *
     * @param TableSchema $schema
     * @param array $row The row data from `describeForeignKeySql`.
     * @return void
     */
    abstract public function convertForeignKeyDescription(TableSchema $schema, array $row);

    /**
     * Convert options data into table options.
     *
     * @param TableSchema $schema Table instance.
     * @param array $row The row of data.
     * @return void
     */
    public function convertOptionsDescription(TableSchema $schema, array $row)
    {
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
    abstract public function createTableSql(TableSchema $schema, array $columns, array $constraints, array $indexes): array;

    /**
     * Generate the SQL fragment for a single column in a table.
     *
     * @param TableSchema $schema The table instance the column is in.
     * @param string $name The name of the column.
     * @return string SQL fragment.
     */
    abstract public function columnSql(TableSchema $schema, string $name): string;

    /**
     * Generate the SQL queries needed to add foreign key constraints to the table
     *
     * @param TableSchema $schema The table instance the foreign key constraints are.
     * @return array SQL fragment.
     */
    abstract public function addConstraintSql(TableSchema $schema): array;

    /**
     * Generate the SQL queries needed to drop foreign key constraints from the table
     *
     * @param TableSchema $schema The table instance the foreign key constraints are.
     * @return array SQL fragment.
     */
    abstract public function dropConstraintSql(TableSchema $schema): array;

    /**
     * Generate the SQL fragments for defining table constraints.
     *
     * @param TableSchema $schema The table instance the column is in.
     * @param string $name The name of the column.
     * @return string SQL fragment.
     */
    abstract public function constraintSql(TableSchema $schema, string $name): string;

    /**
     * Generate the SQL fragment for a single index in a table.
     *
     * @param TableSchema $schema The table object the column is in.
     * @param string $name The name of the column.
     * @return string SQL fragment.
     */
    abstract public function indexSql(TableSchema $schema, string $name): string;

    /**
     * Generate the SQL to truncate a table.
     *
     * @param TableSchema $schema Table instance.
     * @return array SQL statements to truncate a table.
     */
    abstract public function truncateTableSql(TableSchema $schema): array;

}