<?php

namespace Phanda\Database\Schema;

use Phanda\Database\Driver\MysqlDriver;
use Phanda\Exceptions\Database\Schema\SchemaException as Exception;

class MysqlSchema extends AbstractSchema
{

    /**
     * @var MysqlDriver
     */
    protected $driver;

    /**
     * Generate the SQL to list the tables.
     *
     * @param array $config
     * @return array An array of (sql, params) to execute.
     */
    public function listTablesSql(array $config): array
    {
        return ['SHOW TABLES FROM ' . $this->driver->quoteIdentifier($config['database']), []];
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
        return ['SHOW FULL COLUMNS FROM ' . $this->driver->quoteIdentifier($tableName), []];
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
        return ['SHOW INDEXES FROM ' . $this->driver->quoteIdentifier($tableName), []];
    }

    /**
     * {@inheritDoc}
     */
    public function describeOptionsSql(string $tableName, array $config): array
    {
        return ['SHOW TABLE STATUS WHERE Name = ?', [$tableName]];
    }

    /**
     * {@inheritDoc}
     */
    public function convertOptionsDescription(TableSchema $schema, array $row)
    {
        $schema->setOptions([
            'engine' => $row['Engine'],
            'collation' => $row['Collation'],
        ]);
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
        $sql = 'SELECT * FROM information_schema.key_column_usage AS kcu
            INNER JOIN information_schema.referential_constraints AS rc
            ON (
                kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                AND kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
            )
            WHERE kcu.TABLE_SCHEMA = ? AND kcu.TABLE_NAME = ? AND rc.TABLE_NAME = ?';

        return [$sql, [$config['database'], $tableName, $tableName]];
    }

    /**
     * Convert field description results into abstract schema fields.
     *
     * @param TableSchema $schema The table object to append fields to.
     * @param array $row The row data from `describeColumnSql`.
     * @return void
     *
     * @throws Exception
     */
    public function convertColumnDescription(TableSchema $schema, array $row)
    {
        $field = $this->convertColumn($row['Type']);
        $field += [
            'null' => $row['Null'] === 'YES',
            'default' => $row['Default'],
            'collate' => $row['Collation'],
            'comment' => $row['Comment'],
        ];
        if (isset($row['Extra']) && $row['Extra'] === 'auto_increment') {
            $field['autoIncrement'] = true;
        }
        $schema->addColumn($row['Field'], $field);
    }

    /**
     * Convert an index description results into abstract schema indexes or constraints.
     *
     * @param TableSchema $schema
     * @param array $row The row data from `describeIndexSql`.
     * @return void
     *
     * @throws Exception
     */
    public function convertIndexDescription(TableSchema $schema, array $row)
    {
        $type = null;
        $columns = $length = [];

        $name = $row['Key_name'];
        if ($name === 'PRIMARY') {
            $name = $type = TableSchema::CONSTRAINT_PRIMARY;
        }

        $columns[] = $row['Column_name'];

        if ($row['Index_type'] === 'FULLTEXT') {
            $type = TableSchema::INDEX_FULLTEXT;
        } elseif ($row['Non_unique'] == 0 && $type !== 'primary') {
            $type = TableSchema::CONSTRAINT_UNIQUE;
        } elseif ($type !== 'primary') {
            $type = TableSchema::INDEX_INDEX;
        }

        if (!empty($row['Sub_part'])) {
            $length[$row['Column_name']] = $row['Sub_part'];
        }
        $isIndex = (
            $type === TableSchema::INDEX_INDEX ||
            $type === TableSchema::INDEX_FULLTEXT
        );
        if ($isIndex) {
            $existing = $schema->getIndex($name);
        } else {
            $existing = $schema->getConstraint($name);
        }

        // MySQL multi column indexes come back as multiple rows.
        if (!empty($existing)) {
            $columns = array_merge($existing['columns'], $columns);
            $length = array_merge($existing['length'], $length);
        }
        if ($isIndex) {
            $schema->addIndex($name, [
                'type' => $type,
                'columns' => $columns,
                'length' => $length
            ]);
        } else {
            $schema->addConstraint($name, [
                'type' => $type,
                'columns' => $columns,
                'length' => $length
            ]);
        }
    }

    /**
     * Convert a foreign key description into constraints on the Table object.
     *
     * @param TableSchema $schema
     * @param array $row The row data from `describeForeignKeySql`.
     * @return void
     *
     * @throws Exception
     */
    public function convertForeignKeyDescription(TableSchema $schema, array $row)
    {
        $data = [
            'type' => TableSchema::CONSTRAINT_FOREIGN,
            'columns' => [$row['COLUMN_NAME']],
            'references' => [$row['REFERENCED_TABLE_NAME'], $row['REFERENCED_COLUMN_NAME']],
            'update' => $this->convertOnClause($row['UPDATE_RULE']),
            'delete' => $this->convertOnClause($row['DELETE_RULE']),
        ];
        $name = $row['CONSTRAINT_NAME'];
        $schema->addConstraint($name, $data);
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
        $content = implode(",\n", array_merge($columns, $constraints, $indexes));
        $temporary = $schema->isTemporary() ? ' TEMPORARY ' : ' ';
        $content = sprintf("CREATE%sTABLE `%s` (\n%s\n)", $temporary, $schema->name(), $content);
        $options = $schema->getOptions();

        if (isset($options['engine'])) {
            $content .= sprintf(' ENGINE=%s', $options['engine']);
        }

        if (isset($options['charset'])) {
            $content .= sprintf(' DEFAULT CHARSET=%s', $options['charset']);
        }

        if (isset($options['collate'])) {
            $content .= sprintf(' COLLATE=%s', $options['collate']);
        }

        return [$content];
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
        $data = $schema->getColumn($name);
        $out = $this->driver->quoteIdentifier($name);
        $nativeJson = $this->driver->supportsNativeJson();

        $typeMap = [
            TableSchema::TYPE_TINYINTEGER => ' TINYINT',
            TableSchema::TYPE_SMALLINTEGER => ' SMALLINT',
            TableSchema::TYPE_INTEGER => ' INTEGER',
            TableSchema::TYPE_BIGINTEGER => ' BIGINT',
            TableSchema::TYPE_BINARY_UUID => ' BINARY(16)',
            TableSchema::TYPE_BOOLEAN => ' BOOLEAN',
            TableSchema::TYPE_FLOAT => ' FLOAT',
            TableSchema::TYPE_DECIMAL => ' DECIMAL',
            TableSchema::TYPE_DATE => ' DATE',
            TableSchema::TYPE_TIME => ' TIME',
            TableSchema::TYPE_DATETIME => ' DATETIME',
            TableSchema::TYPE_TIMESTAMP => ' TIMESTAMP',
            TableSchema::TYPE_UUID => ' CHAR(36)',
            TableSchema::TYPE_JSON => $nativeJson ? ' JSON' : ' LONGTEXT'
        ];

        $specialMap = [
            'string' => true,
            'text' => true,
            'binary' => true,
        ];

        if (isset($typeMap[$data['type']])) {
            $out .= $typeMap[$data['type']];
        }

        if (isset($specialMap[$data['type']])) {
            switch ($data['type']) {
                case TableSchema::TYPE_STRING:
                    $out .= !empty($data['fixed']) ? ' CHAR' : ' VARCHAR';
                    if (!isset($data['length'])) {
                        $data['length'] = 255;
                    }
                    break;
                case TableSchema::TYPE_TEXT:
                    $isKnownLength = in_array($data['length'], TableSchema::$columnLengths);
                    if (empty($data['length']) || !$isKnownLength) {
                        $out .= ' TEXT';
                        break;
                    }

                    if ($isKnownLength) {
                        $length = array_search($data['length'], TableSchema::$columnLengths);
                        $out .= ' ' . strtoupper($length) . 'TEXT';
                    }

                    break;
                case TableSchema::TYPE_BINARY:
                    $isKnownLength = in_array($data['length'], TableSchema::$columnLengths);
                    if ($isKnownLength) {
                        $length = array_search($data['length'], TableSchema::$columnLengths);
                        $out .= ' ' . strtoupper($length) . 'BLOB';
                        break;
                    }

                    if (empty($data['length'])) {
                        $out .= ' BLOB';
                        break;
                    }

                    if ($data['length'] > 2) {
                        $out .= ' VARBINARY(' . $data['length'] . ')';
                    } else {
                        $out .= ' BINARY(' . $data['length'] . ')';
                    }
                    break;
            }
        }

        $hasLength = [
            TableSchema::TYPE_INTEGER,
            TableSchema::TYPE_SMALLINTEGER,
            TableSchema::TYPE_TINYINTEGER,
            TableSchema::TYPE_STRING
        ];

        if (in_array($data['type'], $hasLength, true) && isset($data['length'])) {
            $out .= '(' . (int)$data['length'] . ')';
        }

        $hasPrecision = [TableSchema::TYPE_FLOAT, TableSchema::TYPE_DECIMAL];

        if (in_array($data['type'], $hasPrecision, true) &&
            (isset($data['length']) || isset($data['precision']))
        ) {
            $out .= '(' . (int)$data['length'] . ',' . (int)$data['precision'] . ')';
        }

        $hasUnsigned = [
            TableSchema::TYPE_TINYINTEGER,
            TableSchema::TYPE_SMALLINTEGER,
            TableSchema::TYPE_INTEGER,
            TableSchema::TYPE_BIGINTEGER,
            TableSchema::TYPE_FLOAT,
            TableSchema::TYPE_DECIMAL
        ];

        if (in_array($data['type'], $hasUnsigned, true) &&
            isset($data['unsigned']) && $data['unsigned'] === true
        ) {
            $out .= ' UNSIGNED';
        }

        $hasCollate = [
            TableSchema::TYPE_TEXT,
            TableSchema::TYPE_STRING,
        ];

        if (in_array($data['type'], $hasCollate, true) && isset($data['collate']) && $data['collate'] !== '') {
            $out .= ' COLLATE ' . $data['collate'];
        }

        if (isset($data['null']) && $data['null'] === false) {
            $out .= ' NOT NULL';
        }

        $addAutoIncrement = (
            [$name] == $schema->primaryKey() &&
            !$schema->hasAutoincrement() &&
            !isset($data['autoIncrement'])
        );

        if (in_array($data['type'], [TableSchema::TYPE_INTEGER, TableSchema::TYPE_BIGINTEGER]) &&
            ($data['autoIncrement'] === true || $addAutoIncrement)
        ) {
            $out .= ' AUTO_INCREMENT';
        }

        if (isset($data['null']) && $data['null'] === true && $data['type'] === TableSchema::TYPE_TIMESTAMP) {
            $out .= ' NULL';
            unset($data['default']);
        }

        if (isset($data['default']) &&
            in_array($data['type'], [TableSchema::TYPE_TIMESTAMP, TableSchema::TYPE_DATETIME]) &&
            in_array(strtolower($data['default']), ['current_timestamp', 'current_timestamp()'])
        ) {
            $out .= ' DEFAULT CURRENT_TIMESTAMP';
            unset($data['default']);
        }

        if (isset($data['default'])) {
            $out .= ' DEFAULT ' . $this->driver->schemaValue($data['default']);
            unset($data['default']);
        }

        if (isset($data['comment']) && $data['comment'] !== '') {
            $out .= ' COMMENT ' . $this->driver->schemaValue($data['comment']);
        }

        return $out;
    }

    /**
     * Generate the SQL queries needed to add foreign key constraints to the table
     *
     * @param TableSchema $schema The table instance the foreign key constraints are.
     * @return array SQL fragment.
     */
    public function addConstraintSql(TableSchema $schema): array
    {
        $sqlPattern = 'ALTER TABLE %s ADD %s;';
        $sql = [];

        foreach ($schema->constraints() as $name) {
            $constraint = $schema->getConstraint($name);
            if ($constraint['type'] === TableSchema::CONSTRAINT_FOREIGN) {
                $tableName = $this->driver->quoteIdentifier($schema->name());
                $sql[] = sprintf($sqlPattern, $tableName, $this->constraintSql($schema, $name));
            }
        }

        return $sql;
    }

    /**
     * Generate the SQL queries needed to drop foreign key constraints from the table
     *
     * @param TableSchema $schema The table instance the foreign key constraints are.
     * @return array SQL fragment.
     */
    public function dropConstraintSql(TableSchema $schema): array
    {
        $sqlPattern = 'ALTER TABLE %s DROP FOREIGN KEY %s;';
        $sql = [];

        foreach ($schema->constraints() as $name) {
            $constraint = $schema->getConstraint($name);
            if ($constraint['type'] === TableSchema::CONSTRAINT_FOREIGN) {
                $tableName = $this->driver->quoteIdentifier($schema->name());
                $constraintName = $this->driver->quoteIdentifier($name);
                $sql[] = sprintf($sqlPattern, $tableName, $constraintName);
            }
        }

        return $sql;
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
        $data = $schema->getConstraint($name);
        if ($data['type'] === TableSchema::CONSTRAINT_PRIMARY) {
            $columns = array_map(
                [$this->driver, 'quoteIdentifier'],
                $data['columns']
            );

            return sprintf('PRIMARY KEY (%s)', implode(', ', $columns));
        }

        $out = '';
        if ($data['type'] === TableSchema::CONSTRAINT_UNIQUE) {
            $out = 'UNIQUE KEY ';
        }
        if ($data['type'] === TableSchema::CONSTRAINT_FOREIGN) {
            $out = 'CONSTRAINT ';
        }
        $out .= $this->driver->quoteIdentifier($name);

        return $this->keySql($out, $data);
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
        $data = $schema->getIndex($name);
        $out = '';
        if ($data['type'] === TableSchema::INDEX_INDEX) {
            $out = 'KEY ';
        }
        if ($data['type'] === TableSchema::INDEX_FULLTEXT) {
            $out = 'FULLTEXT KEY ';
        }
        $out .= $this->driver->quoteIdentifier($name);

        return $this->keySql($out, $data);
    }

    /**
     * Generate the SQL to truncate a table.
     *
     * @param TableSchema $schema Table instance.
     * @return array SQL statements to truncate a table.
     */
    public function truncateTableSql(TableSchema $schema): array
    {
        return [sprintf('TRUNCATE TABLE `%s`', $schema->name())];
    }

    /**
     * Helper method for generating key SQL snippets.
     *
     * @param string $prefix The key prefix
     * @param array $data Key data.
     * @return string
     */
    protected function keySql(string $prefix, array $data): string
    {
        $columns = array_map(
            [$this->driver, 'quoteIdentifier'],
            $data['columns']
        );
        foreach ($data['columns'] as $i => $column) {
            if (isset($data['length'][$column])) {
                $columns[$i] .= sprintf('(%d)', $data['length'][$column]);
            }
        }
        if ($data['type'] === TableSchema::CONSTRAINT_FOREIGN) {
            return $prefix . sprintf(
                    ' FOREIGN KEY (%s) REFERENCES %s (%s) ON UPDATE %s ON DELETE %s',
                    implode(', ', $columns),
                    $this->driver->quoteIdentifier($data['references'][0]),
                    $this->convertConstraintColumns($data['references'][1]),
                    $this->foreignOnClause($data['update']),
                    $this->foreignOnClause($data['delete'])
                );
        }

        return $prefix . ' (' . implode(', ', $columns) . ')';
    }

    /**
     * Convert a MySQL column type into an abstract type.
     *
     * The returned type will be a type that Cake\Database\Type can handle.
     *
     * @param string $column The column type + length
     * @return array Array of column information.
     *
     * @throws Exception
     */
    protected function convertColumn($column)
    {
        preg_match('/([a-z]+)(?:\(([0-9,]+)\))?\s*([a-z]+)?/i', $column, $matches);
        if (empty($matches)) {
            throw new Exception(sprintf('Unable to parse column type from "%s"', $column));
        }

        $col = strtolower($matches[1]);
        $length = $precision = null;
        if (isset($matches[2])) {
            $length = $matches[2];
            if (strpos($matches[2], ',') !== false) {
                list($length, $precision) = explode(',', $length);
            }
            $length = (int)$length;
            $precision = (int)$precision;
        }

        if (in_array($col, ['date', 'time', 'datetime', 'timestamp'])) {
            return ['type' => $col, 'length' => null];
        }
        if (($col === 'tinyint' && $length === 1) || $col === 'boolean') {
            return ['type' => TableSchema::TYPE_BOOLEAN, 'length' => null];
        }

        $unsigned = (isset($matches[3]) && strtolower($matches[3]) === 'unsigned');
        if (strpos($col, 'bigint') !== false || $col === 'bigint') {
            return ['type' => TableSchema::TYPE_BIGINTEGER, 'length' => $length, 'unsigned' => $unsigned];
        }
        if ($col === 'tinyint') {
            return ['type' => TableSchema::TYPE_TINYINTEGER, 'length' => $length, 'unsigned' => $unsigned];
        }
        if ($col === 'smallint') {
            return ['type' => TableSchema::TYPE_SMALLINTEGER, 'length' => $length, 'unsigned' => $unsigned];
        }
        if (in_array($col, ['int', 'integer', 'mediumint'])) {
            return ['type' => TableSchema::TYPE_INTEGER, 'length' => $length, 'unsigned' => $unsigned];
        }
        if ($col === 'char' && $length === 36) {
            return ['type' => TableSchema::TYPE_UUID, 'length' => null];
        }
        if ($col === 'char') {
            return ['type' => TableSchema::TYPE_STRING, 'fixed' => true, 'length' => $length];
        }
        if (strpos($col, 'char') !== false) {
            return ['type' => TableSchema::TYPE_STRING, 'length' => $length];
        }
        if (strpos($col, 'text') !== false) {
            $lengthName = substr($col, 0, -4);
            $length = isset(TableSchema::$columnLengths[$lengthName]) ? TableSchema::$columnLengths[$lengthName] : null;

            return ['type' => TableSchema::TYPE_TEXT, 'length' => $length];
        }
        if ($col === 'binary' && $length === 16) {
            return ['type' => TableSchema::TYPE_BINARY_UUID, 'length' => null];
        }
        if (strpos($col, 'blob') !== false || in_array($col, ['binary', 'varbinary'])) {
            $lengthName = substr($col, 0, -4);
            $length = isset(TableSchema::$columnLengths[$lengthName]) ? TableSchema::$columnLengths[$lengthName] : $length;

            return ['type' => TableSchema::TYPE_BINARY, 'length' => $length];
        }
        if (strpos($col, 'float') !== false || strpos($col, 'double') !== false) {
            return [
                'type' => TableSchema::TYPE_FLOAT,
                'length' => $length,
                'precision' => $precision,
                'unsigned' => $unsigned
            ];
        }
        if (strpos($col, 'decimal') !== false) {
            return [
                'type' => TableSchema::TYPE_DECIMAL,
                'length' => $length,
                'precision' => $precision,
                'unsigned' => $unsigned
            ];
        }

        if (strpos($col, 'json') !== false) {
            return ['type' => TableSchema::TYPE_JSON, 'length' => null];
        }

        return ['type' => TableSchema::TYPE_STRING, 'length' => null];
    }
}