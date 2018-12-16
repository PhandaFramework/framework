<?php

namespace Phanda\Dictionary\Iterator;

use ArrayIterator;
use Phanda\Dictionary\Dictionary;

class ExtractIterator extends Dictionary
{
    /**
     * A callable responsible for extracting a single value for each
     * item in the collection.
     *
     * @var callable
     */
    protected $extractor;

    /**
     * Creates the iterator that will return the requested property for each value
     * in the dictionary expressed in $path
     *
     * @param array|\Traversable $items
     * @param string $path
     */
    public function __construct($items, string $path)
    {
        $this->extractor = $this->propertyExtractor($path);
        parent::__construct($items);
    }

    /**
     * Returns the column value defined in $path or null if the path could not be
     * followed
     *
     * @return mixed
     */
    public function current()
    {
        $extractor = $this->extractor;
        return $extractor(parent::current());
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
        $iterator = $this->getInnerIterator();

        if ($iterator instanceof \Phanda\Contracts\Dictionary\Dictionary) {
            $iterator = $iterator->unwrap();
        }

        if (get_class($iterator) !== ArrayIterator::class) {
            return $this;
        }

        $callback = $this->extractor;
        $res = [];

        foreach ($iterator->getArrayCopy() as $k => $v) {
            $res[$k] = $callback($v);
        }

        return new ArrayIterator($res);
    }
}