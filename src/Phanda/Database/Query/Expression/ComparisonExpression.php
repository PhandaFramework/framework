<?php

namespace Phanda\Database\Query\Expression;

use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Contracts\Database\Query\Expression\Field as FieldContract;
use Phanda\Database\ValueBinder;

class ComparisonExpression implements ExpressionContract, FieldContract
{

    /**
     * @var string|ExpressionContract
     */
    protected $field;

    /**
     * The value to be used in the right hand side of the operation
     *
     * @var mixed
     */
    protected $value;

    /**
     * The operator to be used in this operation to compare field and value
     *
     * @var string
     */
    protected $operator;

    /**
     * ComparisonExpression constructor.
     * @param string|ExpressionContract $field
     * @param mixed $value
     * @param string $operator
     */
    public function __construct($field, $value, string $operator)
    {
        $this->setFieldName($field);
        $this->setValue($value);
        $this->setOperator($operator);
    }

    public function setValue($value): ComparisonExpression
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setOperator($operator): ComparisonExpression
    {
        $this->operator = $operator;
        return $this;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder)
    {
        $field = $this->field;

        if ($field instanceof ExpressionContract) {
            $field = $field->toSql($valueBinder);
        }

        if ($this->value instanceof ExpressionContract) {
            $template = '%s %s (%s)';
            $value = $this->value->toSql($valueBinder);
        } else {
            list($template, $value) = $this->getStringExpression($valueBinder);
        }

        return sprintf($template, $field, $this->operator, $value);
    }

    /**
     * Gets the string value of the current comparison
     *
     * @param ValueBinder $valueBinder
     * @return array
     */
    protected function getStringExpression(ValueBinder $valueBinder)
    {
        $template = '%s ';

        if ($this->field instanceof ExpressionContract) {
            $template = '(%s) ';
        }

        $template .= '%s %s';
        $value = $this->bindValue($this->value, $valueBinder);

        return [$template, $value];
    }

    /**
     * @param callable $visitor
     * @return $this
     */
    public function traverse(callable $visitor)
    {
        if ($this->field instanceof ExpressionContract) {
            $visitor($this->field);
            $this->field->traverse($visitor);
        }

        if ($this->value instanceof ExpressionContract) {
            $visitor($this->value);
            $this->value->traverse($visitor);
        }
        
        return $this;
    }

    /**
     * Sets the fields name
     *
     * @param $field
     * @return FieldContract
     */
    public function setFieldName($field): FieldContract
    {
        $this->field = $field;
        return $this;
    }

    /**
     * Gets the fields name
     *
     * @return mixed
     */
    public function getFieldName()
    {
        return $this->field;
    }

    /**
     * Create a deep clone.
     *
     * Clones the field and value if they are expression objects.
     *
     * @return void
     */
    public function __clone()
    {
        foreach (['value', 'field'] as $prop) {
            if ($prop instanceof ExpressionContract) {
                $this->{$prop} = clone $this->{$prop};
            }
        }
    }

    /**
     * Binds a value and returns the placeholder in the query
     *
     * @param mixed $value
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function bindValue($value, ValueBinder $valueBinder)
    {
        $placeholder = $valueBinder->generatePlaceholderToken('c');
        $valueBinder->bind($placeholder, $value);
        return $placeholder;
    }

    /**
     * Flattens a traversable value and returns the many placeholders for them
     *
     * @param $value
     * @param ValueBinder $valueBinder
     * @return string
     */
    protected function flattenValue($value, ValueBinder $valueBinder)
    {
        $parts = [];

        if (!empty($value)) {
            $parts += $valueBinder->generateManyPlaceholdersForValues($value);
        }

        return implode(',', $parts);
    }
}