<?php


namespace Phanda\Foundation\Http;

use Phanda\Contracts\Support\Arrayable;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest implements Arrayable, \ArrayAccess
{

    /**
     * @return array
     */
    public function toArray()
    {
        return [];
    }

    /**
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return false;
    }

    /**
     * @param mixed $offset
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {

    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {

    }
}