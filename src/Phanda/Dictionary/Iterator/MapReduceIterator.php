<?php

namespace Phanda\Dictionary\Iterator;

use LogicException;
use Traversable;

class MapReduceIterator implements \IteratorAggregate
{

    /**
     * @var array
     */
    protected $intermediate = [];

    /**
     * @var array
     */
    protected $result = [];

    /**
     * @var bool
     */
    protected $executed = false;

    /**
     * @var Traversable|null
     */
    protected $data;

    /**
     * @var callable
     */
    protected $mapper;

    /**
     * @var callable|null
     */
    protected $reducer;

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * MapReduceIterator constructor.
     * @param Traversable $data
     * @param callable $mapper
     * @param callable|null $reducer
     */
    public function __construct(Traversable $data, callable $mapper, ?callable $reducer = null)
    {
        $this->data = $data;
        $this->mapper = $mapper;
        $this->reducer = $reducer;
    }

    /**
     * Retrieve an external iterator
     *
     * @return Traversable
     */
    public function getIterator()
    {
        if(!$this->executed) {
            $this->execute();
        }

        return new \ArrayIterator($this->result);
    }

    /**
     * Appends a result to the intermediate by key
     *
     * @param mixed $val
     * @param string $key
     * @return MapReduceIterator
     */
    public function appendIntermediate($val, string $key): MapReduceIterator
    {
        $this->intermediate[$key][] = $val;
        return $this;
    }

    /**
     * Appends a result to the iterator
     *
     * @param mixed $val
     * @param string|null $key
     * @return MapReduceIterator
     */
    public function append($val, ?string $key = null): MapReduceIterator
    {
        $this->result[$key ?? $this->counter] = $val;
        $this->counter++;
        return $this;
    }

    /**
     * Executes the Map-Reduce algorithm.
     */
    protected function execute()
    {
        $mapper = $this->mapper;

        foreach ($this->data as $key => $val) {
            $mapper($val, $key, $this);
        }

        $this->data = null;

        if (!empty($this->intermediate) && empty($this->reducer)) {
            throw new LogicException('No reducer function was provided for the intermediate');
        }

        $reducer = $this->reducer;

        foreach ($this->intermediate as $key => $list) {
            $reducer($list, $key, $this);
        }

        $this->intermediate = [];
        $this->executed = true;
    }
}