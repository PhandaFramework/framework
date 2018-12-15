<?php

namespace Phanda\Contracts\Dictionary;

interface Dictionary
{
    /**
     * Gets all the items in the collection
     *
     * @return array
     */
    public function all();

    /**
     * Converts the Dictionary to an array
     *
     * @return array
     */
    public function toArray();

    /**
     * Run a map over each of the items in the dictionary.
     *
     * @param callable $callback
     * @return Dictionary
     */
    public function map(callable $callback);

    /**
     * Pushes an item to the end of the array
     *
     * @param $value
     * @return Dictionary
     */
    public function push($value);

    /**
     * Checks if an offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset);

    /**
     * Gets an offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset);

    /**
     * Sets an offset
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value);

    /**
     * Unsets an offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset);

    /**
     * Counts the elements in the dictionary.
     *
     * @return int
     */
    public function count();
}