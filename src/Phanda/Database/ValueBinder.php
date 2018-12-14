<?php

namespace Phanda\Database;

use Phanda\Contracts\Database\Statement;
use Phanda\Support\PhandArr;

class ValueBinder
{
    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * @var int
     */
    protected $bindingCount = 0;

    /**
     * Binds a parameter to a value.
     *
     * @param string|int $param
     * @param mixed $value
     * @return ValueBinder
     */
    public function bind($param, $value)
    {
        $this->bindings[$param] = PhandArr::makeArray($value) + [
                'placeholder' => is_int($param) ? $param : substr($param, 1)
            ];
        return $this;
    }

    /**
     * Generates a placeholder token
     *
     * @param $token
     * @return string
     */
    public function generatePlaceholderToken($token)
    {
        $number = $this->bindingCount++;
        if ($token[0] !== ':' && $token !== '?') {
            $token = sprintf(':%s%s', $token, $number);
        }

        return $token;
    }

    /**
     * Generates many placeholders for values
     *
     * @param $values
     * @return array
     */
    public function generateManyPlaceholdersForValues($values)
    {
        $placeholders = [];
        foreach ($values as $k => $value) {
            $param = $this->generatePlaceholderToken('c');
            $this->bindings[$param] = [
                'value' => $value,
                'placeholder' => substr($param, 1),
            ];
            $placeholders[$k] = $param;
        }

        return $placeholders;
    }

    /**
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->bindings = [];
        $this->bindingCount = 0;
        return $this;
    }

    /**
     * @return $this
     */
    public function resetBindingCount()
    {
        $this->bindingCount = 0;
        return $this;
    }

    /**
     * @param Statement $statement
     * @return $this
     */
    public function attachToStatement(Statement $statement)
    {
        $bindings = $this->getBindings();
        if (empty($bindings)) {
            return $this;
        }

        foreach ($bindings as $binding) {
            $statement->bindValue($binding['placeholder'], $binding['value']);
        }

        return $this;
    }
}