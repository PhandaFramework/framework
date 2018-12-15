<?php

namespace Phanda\Database\Query\Expression;

use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Contracts\Database\Query\Expression\Field as FieldContract;
use Phanda\Database\ValueBinder;

class BetweenExpression implements ExpressionContract, FieldContract
{

    /**
     * @var string|ExpressionContract
     */
    protected $field;

    /**
     * @var mixed
     */
    protected $min;

    /**
     * @var mixed
     */
    protected $max;

    /**
     * BetweenExpression constructor.
     *
     * @param string|ExpressionContract $field
     * @param mixed $min
     * @param mixed $max
     */
    public function __construct($field, $min, $max)
    {
        $this->setFieldName($field);
        $this->setMin($min);
        $this->setMax($max);
    }

    /**
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder)
    {
        $parts = [
            'min' => $this->min,
            'max' => $this->max
        ];

        $field = $this->field;
        if ($field instanceof ExpressionContract) {
            $field = $field->toSql($valueBinder);
        }

        foreach ($parts as $name => $part) {
            if ($part instanceof ExpressionContract) {
                $parts[$name] = $part->toSql($valueBinder);
                continue;
            }
            $parts[$name] = $this->bindValue($part, $valueBinder);
        }

        return sprintf('%s BETWEEN %s AND %s', $field, $parts['min'], $parts['max']);
    }

    /**
     * @param callable $visitor
     * @return $this
     */
    public function traverse(callable $visitor)
    {
        foreach ([$this->field, $this->min, $this->max] as $part) {
            if ($part instanceof ExpressionContract) {
                $visitor($part);
            }
        }

        return $this;
    }

    /**
     * Binds a value and returns the placeholder token for that value
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
     * Do a deep clone of this expression.
     *
     * @return void
     */
    public function __clone()
    {
        foreach (['field', 'min', 'max'] as $part) {
            if ($this->{$part} instanceof ExpressionContract) {
                $this->{$part} = clone $this->{$part};
            }
        }
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
     * @param mixed $max
     * @return BetweenExpression
     */
    public function setMax($max): BetweenExpression
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param mixed $min
     * @return BetweenExpression
     */
    public function setMin($min): BetweenExpression
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }
}