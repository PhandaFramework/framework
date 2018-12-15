<?php

namespace Phanda\Dictionary;

use Phanda\Contracts\Support\Arrayable;
use Phanda\Contracts\Support\Jsonable;

class Dictionary implements Arrayable, \ArrayAccess, \Countable, \Phanda\Contracts\Dictionary\Dictionary
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * Dictionary constructor.
     *
     * @param mixed $items
     */
    public function __construct($items = [])
    {
        $this->items = $this->convertItemsToArray($items);
    }

    /**
     * @param $items
     * @return array
     */
    protected function convertItemsToArray($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->toArray();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof \JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof \Traversable) {
            return iterator_to_array($items);
        }

        return (array)$items;
    }

    /**
     * Gets all the items in the collection
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Converts the Dictionary to an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * Run a map over each of the items in the dictionary.
     *
     * @param callable $callback
     * @return \Phanda\Contracts\Dictionary\Dictionary
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);
        return new static(array_combine($keys, $items));
    }

    /**
     * Pushes an item to the end of the array
     *
     * @param $value
     * @return \Phanda\Contracts\Dictionary\Dictionary
     */
    public function push($value)
    {
        $this->offsetSet(null, $value);
        return $this;
    }

    /**
     * Checks if an offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Gets an offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Sets an offset
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * Counts the elements in the dictionary.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }
}