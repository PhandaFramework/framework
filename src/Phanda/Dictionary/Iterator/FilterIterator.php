<?php

namespace Phanda\Dictionary\Iterator;

use ArrayIterator;
use CallbackFilterIterator;
use Iterator;
use Phanda\Dictionary\Dictionary;

class FilterIterator extends Dictionary
{
    /**
     * The callback used to filter the elements in this collection
     *
     * @var callable
     */
    protected $callback;

    /**
     * Creates a filtered iterator using the callback to determine which items are
     * accepted or rejected.
     *
     * Each time the callback is executed it will receive the value of the element
     * in the current iteration, the key of the element and the passed $items iterator
     * as arguments, in that order.
     *
     * @param \Iterator $items The items to be filtered.
     * @param callable $callback Callback.
     */
    public function __construct($items, callable $callback)
    {
        if (!$items instanceof Iterator) {
            $items = new Dictionary($items);
        }

        $this->callback = $callback;
        $wrapper = new CallbackFilterIterator($items, $callback);
        parent::__construct($wrapper);
    }

    /**
     * {@inheritDoc}
     *
     * We perform here some strictness analysis so that the
     * iterator logic is bypassed entirely.
     *
     * @return \Iterator
     */
    public function unwrap()
    {
        /** @var \IteratorIterator $filter */
        $filter = $this->getInnerIterator();
        $iterator = $filter->getInnerIterator();

        if ($iterator instanceof \Phanda\Contracts\Dictionary\Dictionary) {
            $iterator = $iterator->unwrap();
        }

        if (get_class($iterator) !== ArrayIterator::class) {
            return $filter;
        }

        $callback = $this->callback;
        $res = [];

        foreach ($iterator as $k => $v) {
            if ($callback($v, $k, $iterator)) {
                $res[$k] = $v;
            }
        }

        return new ArrayIterator($res);
    }
}