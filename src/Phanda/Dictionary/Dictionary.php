<?php

namespace Phanda\Dictionary;

use ArrayIterator;
use InvalidArgumentException;
use JsonSerializable;
use Phanda\Contracts\Dictionary\Dictionary as DictionaryContract;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Contracts\Support\Jsonable;
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
			$items = $this->makeItemsArray($items);
		}

		parent::__construct($items);
	}

	protected function makeItemsArray($items)
	{
		if (is_array($items)) {
			return $items;
		} elseif ($items instanceof self) {
			return $items->all();
		} elseif ($items instanceof Arrayable) {
			return $items->toArray();
		} elseif ($items instanceof Jsonable) {
			return json_decode($items->toJson(), true);
		} elseif ($items instanceof JsonSerializable) {
			return $items->jsonSerialize();
		} elseif ($items instanceof Traversable) {
			return iterator_to_array($items);
		}
		return (array) $items;
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