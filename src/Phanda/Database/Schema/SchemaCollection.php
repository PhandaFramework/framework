<?php

namespace Phanda\Database\Schema;

use PDOException;
use Phanda\Contracts\Database\Connection\Connection;
use Phanda\Contracts\Database\Statement;
use Phanda\Exceptions\Database\Schema\SchemaException as Exception;

class SchemaCollection
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var AbstractSchema
     */
    protected $schema;

    public function __construct(Connection $connection)
    {
        $this->setConnection($connection);

        $this->setSchema(
            $this->getConnection()
                ->getDriver()
                ->getSchemaDialect()
        );
    }

    /**
     * @param Connection $connection
     * @return SchemaCollection
     */
    public function setConnection(Connection $connection): SchemaCollection
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param AbstractSchema $schema
     * @return SchemaCollection
     */
    public function setSchema(AbstractSchema $schema): SchemaCollection
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * @return AbstractSchema
     */
    public function getSchema(): AbstractSchema
    {
        return $this->schema;
    }

    /**
     * Get the list of tables available in the current connection.
     *
     * @return array The list of tables in the connected database/schema.
     */
    public function listTables(): array
    {
        list($sql, $params) = $this->schema->listTablesSql($this->connection->getConfiguration());
        $result = [];
        $statement = $this->connection->executeSqlWithParams($sql, $params);

        while ($row = $statement->fetch()) {
            $result[] = $row[0];
        }
        $statement->closeCursor();

        return $result;
    }

    /**
     * Get the column metadata for a table.
     *
     * @param string $name The name of the table to describe.
     * @param array $options The options to use, see above.
     * @return TableSchema Object with column metadata.
     *
     * @throws Exception
     */
    public function describe(string $name, array $options = []): TableSchema
    {
        $config = $this->connection->getConfiguration();

        if (strpos($name, '.')) {
            list($config['schema'], $name) = explode('.', $name);
        }

        $table = new TableSchema($name);

        $this->reflect('Column', $name, $config, $table);

        if (count($table->columns()) === 0) {
            throw new Exception(sprintf('Cannot describe %s. It has 0 columns.', $name));
        }

        $this->reflect('Index', $name, $config, $table);
        $this->reflect('ForeignKey', $name, $config, $table);
        $this->reflect('Options', $name, $config, $table);

        return $table;
    }

    /**
     * Helper method for running each step of the reflection process.
     *
     * @param string $stage The stage name.
     * @param string $name The table name.
     * @param array $config The config data.
     * @param TableSchema $schema The table instance
     * @return void
     *
     * @throws Exception
     */
    protected function reflect(string $stage, string $name, array $config, TableSchema $schema)
    {
        $describeMethod = "describe{$stage}Sql";
        $convertMethod = "convert{$stage}Description";

        list($sql, $params) = $this->schema->{$describeMethod}($name, $config);

        if (empty($sql)) {
            return;
        }

        try {
            $statement = $this->connection->executeSqlWithParams($sql, $params);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }

        foreach ($statement->fetchAll(Statement::FETCH_TYPE_ASSOC) as $row) {
            $this->schema->{$convertMethod}($schema, $row);
        }

        $statement->closeCursor();
    }

}