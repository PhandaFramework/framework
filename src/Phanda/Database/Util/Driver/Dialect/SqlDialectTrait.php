<?php

namespace Phanda\Database\Util\Driver\Dialect;

use Phanda\Contracts\Database\Query\Query;
use Phanda\Database\Query\Expression\ComparisonExpression;
use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;

/**
 * Trait SqlDialectTrait
 * @package Phanda\Database\Util\Driver\Dialect
 */
trait SqlDialectTrait
{
    /**
     * Quotes a database identifier (a column name, table name, etc..) to
     * be used safely in queries without the risk of using reserved words.
     *
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);

        if ($identifier === '*' || $identifier === '') {
            return $identifier;
        }

        // string
        if (preg_match('/^[\w-]+$/u', $identifier)) {
            return $this->startQuote . $identifier . $this->endQuote;
        }

        // string.string
        if (preg_match('/^[\w-]+\.[^ \*]*$/u', $identifier)) {
            $items = explode('.', $identifier);

            return $this->startQuote . implode($this->endQuote . '.' . $this->startQuote, $items) . $this->endQuote;
        }

        // string.*
        if (preg_match('/^[\w-]+\.\*$/u', $identifier)) {
            return $this->startQuote . str_replace('.*', $this->endQuote . '.*', $identifier);
        }

        // Functions
        if (preg_match('/^([\w-]+)\((.*)\)$/', $identifier, $matches)) {
            return $matches[1] . '(' . $this->quoteIdentifier($matches[2]) . ')';
        }

        // Alias.field AS thing
        if (preg_match('/^([\w-]+(\.[\w\s-]+|\(.*\))*)\s+AS\s*([\w-]+)$/ui', $identifier, $matches)) {
            return $this->quoteIdentifier($matches[1]) . ' AS ' . $this->quoteIdentifier($matches[3]);
        }

        // string.string with spaces
        if (preg_match('/^([\w-]+\.[\w][\w\s\-]*[\w])(.*)/u', $identifier, $matches)) {
            $items = explode('.', $matches[1]);
            $field = implode($this->endQuote . '.' . $this->startQuote, $items);

            return $this->startQuote . $field . $this->endQuote . $matches[2];
        }

        if (preg_match('/^[\w_\s-]*[\w_-]+/u', $identifier)) {
            return $this->startQuote . $identifier . $this->endQuote;
        }

        return $identifier;
    }

    /**
     * Returns a callable function that will be used to transform a passed Query object.
     *
     * @param string $type
     * @return callable
     */
    public function queryTranslator($type): callable
    {
        return function ($query) use ($type) {
            /** @var Query $query */
            $query = $this->{$type . 'QueryTranslator'}($query);
            $translators = $this->expressionTranslators();

            if (!$translators) {
                return $query;
            }

            $query->traverseExpressions(function ($expression) use ($translators, $query) {
                foreach ($translators as $class => $method) {
                    if ($expression instanceof $class) {
                        $this->{$method}($expression, $query);
                    }
                }
            });

            return $query;
        };
    }

    /**
     * Returns an associative array of methods that will transform Expression
     * objects to conform with the specific SQL dialect. Keys are class names
     * and values a method in this class.
     *
     * @return array
     */
    protected function expressionTranslators(): array
    {
        return [];
    }

    /**
     * Apply translation steps to select queries.
     *
     * @param Query $query The query to translate
     * @return Query The modified query
     */
    protected function selectQueryTranslator($query): Query
    {
        return $this->transformDistinct($query);
    }

    /**
     * Returns the passed query after rewriting the DISTINCT clause
     *
     * @param Query $query The query to be transformed
     * @return Query
     */
    protected function transformDistinct($query): Query
    {
        if (is_array($query->getClause('distinct'))) {
            $query->groupBy($query->getClause('distinct'), true);
            $query->distinct(false);
        }

        return $query;
    }

    /**
     * Apply translation steps to delete queries.
     *
     * @param Query $query
     * @return Query
     */
    protected function deleteQueryTranslator($query): Query
    {
        $hadAlias = false;
        $tables = [];

        foreach ($query->getClause('from') as $alias => $table) {
            if (is_string($alias)) {
                $hadAlias = true;
            }

            $tables[] = $table;
        }

        if ($hadAlias) {
            $query->from($tables, true);
        }

        if (!$hadAlias) {
            return $query;
        }

        return $this->removeAliasesFromConditions($query);
    }

    /**
     * Apply translation steps to update queries.
     *
     * @param Query $query
     * @return Query
     */
    protected function updateQueryTranslator($query): Query
    {
        return $this->removeAliasesFromConditions($query);
    }

    /**
     * Removes aliases from the `WHERE` clause of a query.
     *
     * @param Query $query
     * @return Query
     * @throws \RuntimeException
     */
    protected function removeAliasesFromConditions($query): Query
    {
        if ($query->getClause('join')) {
            throw new \RuntimeException("Attempting to remove aliases from conditions in an Update/Delete query. This can break references.");
        }

        $conditions = $query->getClause('where');

        if ($conditions) {
            $conditions->traverse(function ($condition) {
                if (!($condition instanceof ComparisonExpression)) {
                    return $condition;
                }

                $field = $condition->getFieldName();

                if ($field instanceof ExpressionContract || strpos($field, '.') === false) {
                    return $condition;
                }

                list(, $field) = explode('.', $field);
                $condition->setFieldName($field);

                return $condition;
            });
        }

        return $query;
    }

    /**
     * Apply translation steps to insert queries.
     *
     * @param Query $query
     * @return Query
     */
    protected function insertQueryTranslator(Query $query): Query
    {
        return $query;
    }

    /**
     * Returns a SQL snippet for creating a new transaction savepoint
     *
     * @param string $name
     * @return string
     */
    public function savePointSQL($name): string
    {
        return 'SAVEPOINT LEVEL' . $name;
    }

    /**
     * Returns a SQL snippet for releasing a previously created save point
     *
     * @param string $name
     * @return string
     */
    public function releaseSavePointSQL($name): string
    {
        return 'RELEASE SAVEPOINT LEVEL' . $name;
    }

    /**
     * Returns a SQL snippet for rolling back a previously created save point
     *
     * @param string $name
     * @return string
     */
    public function rollbackSavePointSQL($name): string
    {
        return 'ROLLBACK TO SAVEPOINT LEVEL' . $name;
    }
}