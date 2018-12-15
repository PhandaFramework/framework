<?php

namespace Phanda\Database\Query\Expression;

use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContact;
use Phanda\Database\ValueBinder;

class UnaryExpression implements ExpressionContact
{

    /**
     * Indicates expression is in pre-order
     */
    const PREFIX = 0;

    /**
     * Indicates expression is in post-order
     */
    const SUFFIX = 1;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var int
     */
    protected $mode;

    /**
     * UnaryExpression constructor.
     *
     * @param string $operator
     * @param mixed $value
     * @param int $mode
     */
    public function __construct($operator, $value, $mode = self::PREFIX)
    {
        $this->operator = $operator;
        $this->value = $value;
        $this->mode = $mode;
    }

    /**
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder)
    {
        $operand = $this->value;
        if ($operand instanceof ExpressionContact) {
            $operand = $operand->toSql($valueBinder);
        }

        if ($this->mode === self::SUFFIX) {
            return '(' . $operand . ') ' . $this->operator;
        }

        return $this->operator . ' (' . $operand . ')';
    }

    /**
     * @param callable $visitor
     * @return $this
     */
    public function traverse(callable $visitor)
    {
        if ($this->value instanceof ExpressionContact) {
            $visitor($this->value);
            $this->value->traverse($visitor);
        }

        return $this;
    }

    /**
     * Perform a deep clone of the inner expression.
     *
     * @return void
     */
    public function __clone()
    {
        if ($this->value instanceof ExpressionContact) {
            $this->value = clone $this->value;
        }
    }
}