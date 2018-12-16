<?php

namespace Phanda\Dictionary\Iterator;

use Phanda\Dictionary\Dictionary;
use RecursiveIterator;

class NoChildrenIterator extends Dictionary implements RecursiveIterator
{

    /**
     * Returns false as there are no children iterators in this collection
     *
     * @return bool
     */
    public function hasChildren()
    {
        return false;
    }

    /**
     * Returns null as there are no children for this iteration level
     *
     * @return null
     */
    public function getChildren()
    {
        return null;
    }
}