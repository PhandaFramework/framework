<?php

namespace Phanda\Database\Query;

use Phanda\Contracts\Database\Query\Query as QueryContract;
use Phanda\Database\Query\Expression\QueryExpression;
use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Contracts\Database\Query\Compiler as QueryCompilerContract;
use Phanda\Database\ValueBinder;
use Phanda\Support\PhandArr;

/**
 * Class QueryCompiler
 * @package Phanda\Database\Query
 *
 * The QueryCompiler handles the compilation of the Query class into
 * SQL.
 *
 * @see \Phanda\Contracts\Database\Query\Query
 */
class QueryCompiler implements QueryCompilerContract
{
    /**
     * List of sprintf templates that are used when compiling
     * the Query class into raw SQL
     *
     * @var array
     */
    protected $templates = [
        'delete' => 'DELETE',
        'where' => ' WHERE %s',
        'group' => ' GROUP BY %s ',
        'having' => ' HAVING %s ',
        'order' => ' %s',
        'limit' => ' LIMIT %s',
        'offset' => ' OFFSET %s',
        'append' => ' %s'
    ];

    /**
     * The list and order of keywords to traverse when building a select query.
     *
     * @var array
     */
    protected $selectKeywords = [
        'select', 'from', 'join', 'where', 'group', 'having', 'order', 'limit',
        'offset', 'union', 'append'
    ];

    /**
     * The list and order of keywords to traverse when building an update query.
     *
     * @var array
     */
    protected $updateKeywords = [
        'update', 'set', 'where', 'append'
    ];

    /**
     * The list and order of keywords to traverse when building a delete query.
     *
     * @var array
     */
    protected $deleteKeywords = [
        'delete', 'modifier', 'from', 'where', 'append'
    ];

    /**
     * The list and order of keywords to traverse when building a insert query.
     *
     * @var array
     */
    protected $insertKeywords = [
        'insert', 'values', 'append'
    ];

    /**
     * Whether or not the current dialect supports ordered unions.
     *
     * @var bool
     */
    protected $orderedUnion = true;

    /**
     * @param QueryContract $query
     * @param ValueBinder $valueBinder
     * @return \Closure|string
     */
    public function compile(QueryContract $query, ValueBinder $valueBinder)
    {
        $sql = '';
        $type = $query->getType();
        $query->traverse(
            $this->sqlCompiler($sql, $query, $valueBinder),
            $this->{$type . 'Keywords'}
        );

        if ($query->getValueBinder() !== $valueBinder) {
            foreach ($query->getValueBinder()->getBindings() as $binding) {
                $placeholder = ':' . $binding['placeholder'];
                if (preg_match('/' . $placeholder . '(?:\W|$)/', $sql) > 0) {
                    $valueBinder->bind($placeholder, $binding['value']);
                }
            }
        }

        return $sql;
    }

    /**
     * Returns a closure that can be used to build a SQL string of the Query object.
     *
     * @param string $sql
     * @param QueryContract $query
     * @param ValueBinder $valueBinder
     * @return \Closure
     */
    protected function sqlCompiler(string &$sql, QueryContract $query, ValueBinder $valueBinder): \Closure
    {
        return function ($parts, $name) use (&$sql, $query, $valueBinder) {
            if (!isset($parts) || ((is_array($parts) || $parts instanceof \Countable) && !count($parts))) {
                return null;
            }

            if ($parts instanceof ExpressionContract) {
                $parts = [$parts->toSql($valueBinder)];
            }

            if (isset($this->_templates[$name])) {
                $parts = $this->stringifyExpressions(PhandArr::makeArray($parts), $valueBinder);
                return $sql .= sprintf($this->templates[$name], implode(', ', $parts));
            }

            return $sql .= $this->{'build' . ucfirst($name) . 'Keyword'}($parts, $query, $valueBinder);
        };
    }

    /**
     * Helper function that converts the ExpressionContract's in an array
     * into their string representation
     *
     * @param array $expressions
     * @param ValueBinder $valueBinder
     * @param bool $wrap
     * @return array
     */
    protected function stringifyExpressions(array $expressions, ValueBinder $valueBinder, $wrap = true): array
    {
        $result = [];

        foreach ($expressions as $k => $expression) {
            if ($expression instanceof ExpressionContract) {
                $value = $expression->toSql($valueBinder);
                $expression = $wrap ? '(' . $value . ')' : $value;
            }

            $result[$k] = $expression;
        }

        return $result;
    }

    /**
     * Builds the SQL for the SELECT keyword
     *
     * @param array $parts
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function buildSelectKeyword(array $parts, Query $query, ValueBinder $valueBinder): string
    {
        $driver = $query->getConnection()->getDriver();
        $select = 'SELECT%s %s%s';

        if ($this->orderedUnion && $query->getClause('union')) {
            $select = '(SELECT%s %s%s';
        }

        $distinct = $query->getClause('distinct');
        $modifiers = $this->buildModifierKeyword($query->getClause('modifier'), $query, $valueBinder);

        $normalized = [];
        $parts = $this->stringifyExpressions($parts, $valueBinder);

        foreach ($parts as $key => $part) {
            if (!is_numeric($key)) {
                $part = $part . ' AS ' . $driver->quoteIdentifier($key);
            }
            $normalized[] = $part;
        }

        if ($distinct === true) {
            $distinct = 'DISTINCT ';
        }

        if (is_array($distinct)) {
            $distinct = $this->stringifyExpressions($distinct, $valueBinder);
            $distinct = sprintf('DISTINCT ON (%s) ', implode(', ', $distinct));
        }

        return sprintf($select, $modifiers, $distinct, implode(', ', $normalized));
    }

    /**
     * Builds the SQL for the FROM keyword
     *
     * @param array $parts
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function buildFromKeyword(array $parts, Query $query, ValueBinder $valueBinder): string
    {
        $select = ' FROM %s';
        $normalized = [];
        $parts = $this->stringifyExpressions($parts, $valueBinder);

        foreach ($parts as $key => $part) {
            if (!is_numeric($key)) {
                $part = $part . ' ' . $key;
            }
            $normalized[] = $part;
        }

        return sprintf($select, implode(', ', $normalized));
    }

    /**
     * Builds the SQL for the JOIN keyword
     *
     * @param array $parts
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function buildJoinKeyword(array $parts, Query $query, ValueBinder $valueBinder): string
    {
        $joins = '';

        foreach ($parts as $join) {
            $subQuery = $join['table'] instanceof Query || $join['table'] instanceof QueryExpression;

            if ($join['table'] instanceof ExpressionContract) {
                $join['table'] = $join['table']->toSql($valueBinder);
            }

            if ($subQuery) {
                $join['table'] = '(' . $join['table'] . ')';
            }

            $joins .= sprintf(' %s JOIN %s %s', $join['type'], $join['table'], $join['alias']);

            $condition = '';

            if (isset($join['conditions']) && $join['conditions'] instanceof ExpressionContract) {
                /** @var ExpressionContract $conditions */
                $conditions = $join['conditions'];
                $condition = $conditions->toSql($valueBinder);
            }

            if (strlen($condition)) {
                $joins .= " ON {$condition}";
            } else {
                $joins .= ' ON 1 = 1';
            }
        }

        return $joins;
    }

    /**
     * Builds the SQL for the SET keyword
     *
     * @param array $parts
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function buildSetKeyword(array $parts, Query $query, ValueBinder $valueBinder): string
    {
        $set = [];

        foreach ($parts as $part) {
            if ($part instanceof ExpressionContract) {
                $part = $part->toSql($valueBinder);
            }

            if ($part[0] === '(') {
                $part = substr($part, 1, -1);
            }

            $set[] = $part;
        }

        return ' SET ' . implode('', $set);
    }

    /**
     * Builds the SQL for the UNION keyword
     *
     * @param array $parts
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function buildUnionKeyword(array $parts, Query $query, ValueBinder $valueBinder): string
    {
        $parts = array_map(function ($part) use ($valueBinder) {
            /** @var ExpressionContract $queryPart */
            $queryPart = $part['query'];
            $part['query'] = $queryPart->toSql($valueBinder);
            $part['query'] = $part['query'][0] === '(' ? trim($part['query'], '()') : $part['query'];
            $prefix = $part['all'] ? 'ALL ' : '';

            if ($this->orderedUnion) {
                return "{$prefix}({$part['query']})";
            }

            return $prefix . $part['query'];
        }, $parts);

        if ($this->orderedUnion) {
            return sprintf(")\nUNION %s", implode("\nUNION ", $parts));
        }

        return sprintf("\nUNION %s", implode("\nUNION ", $parts));
    }

    /**
     * Builds the SQL for the INSERT keyword
     *
     * @param array $parts
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function buildInsertKeyword(array $parts, Query $query, ValueBinder $valueBinder): string
    {

    }

    /**
     * Builds the SQL for the VALUES keyword
     *
     * @param array $parts
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function buildValuesKeyword(array $parts, Query $query, ValueBinder $valueBinder): string
    {

    }

    /**
     * Builds the SQL for the UPDATE keyword
     *
     * @param array $parts
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function buildUpdateKeyword(array $parts, Query $query, ValueBinder $valueBinder): string
    {

    }

    /**
     * Builds the SQL for the MODIFIER keyword
     *
     * @param array $parts
     * @param Query $query
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function buildModifierKeyword(array $parts, Query $query, ValueBinder $valueBinder): string
    {

    }

}