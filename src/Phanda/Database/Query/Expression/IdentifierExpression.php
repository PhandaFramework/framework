<?php

namespace Phanda\Database\Query\Expression;

use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;
use Phanda\Database\ValueBinder;

class IdentifierExpression implements ExpressionContract
{

    /**
     * @var string
     */
    protected $identifier;

    /**
     * IdentifierExpression constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Sets the internal identifier of this expression
     *
     * @param string $identifier
     * @return IdentifierExpression
     */
    public function setIdentifier(string $identifier): IdentifierExpression
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Gets the internal identifier of this expression
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param ValueBinder $valueBinder
     * @return string
     */
    public function toSql(ValueBinder $valueBinder)
    {
        return $this->getIdentifier();
    }

    /**
     * @param callable $visitor
     * @return $this
     */
    public function traverse(callable $visitor)
    {
        return $this;
    }
}