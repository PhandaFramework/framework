<?php

namespace Phanda\Dictionary;

use ArrayIterator;
use Phanda\Contracts\Dictionary\Dictionary as DictionaryContract;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Dictionary\Util\DictionaryTrait;

class Dictionary extends \IteratorIterator implements Arrayable, \ArrayAccess, \Countable, DictionaryContract
{
	use DictionaryTrait;

    /**
     * Dictionary constructor.
     *
     * @param mixed $items
     */
    public function __construct($items = [])
    {
        $this->items = $this->convertItemsToArray($items);

        parent::__construct(new ArrayIterator($this->items));
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