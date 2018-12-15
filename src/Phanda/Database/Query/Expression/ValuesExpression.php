<?php

namespace Phanda\Database\Query\Expression;

use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Database\Query\Query;
use Phanda\Database\ValueBinder;

class ValuesExpression implements ExpressionContract
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var \Phanda\Contracts\Database\Query\Query|null
     */
    protected $query;

    public function __construct(array $columns = [])
    {
        $this->setColumns($columns);
    }

    /**
     * Add a row of data to be inserted.
     *
     * @param array|\Phanda\Contracts\Database\Query\Query $data
     */
    public function add($data)
    {
        if ((count($this->values) && $data instanceof Query) || ($this->query && is_array($data))) {
            throw new \InvalidArgumentException('You cannot mix sub queries and array data in inserts.');
        }

        if ($data instanceof Query) {
            $this->setQuery($data);
            return;
        }

        $this->values[] = $data;
    }

    /**
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder)
    {
        if (empty($this->values) && empty($this->query)) {
            return '';
        }

        $columns = $this->getColumnNames();
        $defaults = array_fill_keys($columns, null);
        $placeholders = [];

        foreach ($this->values as $row) {
            $row += $defaults;
            $rowPlaceholders = [];

            foreach ($columns as $column) {
                $value = $row[$column];

                if ($value instanceof ExpressionContract) {
                    $rowPlaceholders[] = '(' . $value->toSql($valueBinder) . ')';
                    continue;
                }

                $placeholder = $valueBinder->generatePlaceholderToken('c');
                $rowPlaceholders[] = $placeholder;
                $valueBinder->bind($placeholder, $value);
            }

            $placeholders[] = implode(', ', $rowPlaceholders);
        }

        if ($this->getQuery()) {
            return ' ' . $this->getQuery()->toSql($valueBinder);
        }

        return sprintf(' VALUES (%s)', implode('), (', $placeholders));
    }

    /**
     * @param callable $visitor
     * @return $this
     */
    public function traverse(callable $visitor): ValuesExpression
    {
        if ($this->query) {
            return $this;
        }

        foreach ($this->values as $value) {
            if ($value instanceof ExpressionContract) {
                $value->traverse($visitor);
            }

            if (!is_array($value)) {
                continue;
            }

            foreach ($value as $column => $field) {
                if ($field instanceof ExpressionContract) {
                    $visitor($field);
                    $field->traverse($visitor);
                }
            }
        }

        return $this;
    }

    /**
     * @param array $columns
     * @return ValuesExpression
     */
    public function setColumns(array $columns): ValuesExpression
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Gets the column names
     *
     * @return array
     */
    public function getColumnNames(): array
    {
        $columns = [];

        foreach ($this->getColumns() as $col) {
            if (is_string($col)) {
                $col = trim($col, '`[]"');
            }
            $columns[] = $col;
        }

        return $columns;
    }

    /**
     * @param \Phanda\Contracts\Database\Query\Query|null $query
     * @return ValuesExpression
     */
    public function setQuery(?\Phanda\Contracts\Database\Query\Query $query): ValuesExpression
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return \Phanda\Contracts\Database\Query\Query|null
     */
    public function getQuery(): ?\Phanda\Contracts\Database\Query\Query
    {
        return $this->query;
    }

    /**
     * @param array $values
     * @return ValuesExpression
     */
    public function setValues(array $values): ValuesExpression
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }
}