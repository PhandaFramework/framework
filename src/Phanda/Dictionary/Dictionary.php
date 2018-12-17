<?php

namespace Phanda\Dictionary;

use ArrayIterator;
use InvalidArgumentException;
use Phanda\Contracts\Dictionary\Dictionary as DictionaryContract;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Dictionary\Util\DictionaryTrait;
use Traversable;

class Dictionary extends \IteratorIterator implements DictionaryContract, \Serializable
{
	use DictionaryTrait;

    /**
     * Dictionary constructor.
     *
     * @param mixed $items
     */
	public function __construct($items)
	{
		if (is_array($items)) {
			$items = new ArrayIterator($items);
		}

		if (!($items instanceof Traversable)) {
			$msg = 'Only an array or \Traversable is allowed for Dictionary';
			throw new InvalidArgumentException($msg);
		}

		parent::__construct($items);
	}

	/**
	 * Returns a string representation of this object that can be used
	 * to reconstruct it
	 *
	 * @return string
	 */
	public function serialize()
	{
		return serialize($this->buffered());
	}

	/**
	 * Unserializes the passed string and rebuilds the Dictionary instance
	 *
	 * @param string $Dictionary The serialized Dictionary
	 * @return void
	 */
	public function unserialize($Dictionary)
	{
		$this->__construct(unserialize($Dictionary));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return int
	 */
	public function count()
	{
		$traversable = $this->optimizeUnwrap();

		if (is_array($traversable)) {
			return count($traversable);
		}

		return iterator_count($traversable);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return int
	 */
	public function countKeys()
	{
		return count($this->toArray());
	}
}