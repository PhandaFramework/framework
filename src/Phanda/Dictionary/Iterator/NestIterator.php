<?php

namespace Phanda\Dictionary\Iterator;

use Phanda\Dictionary\Dictionary;
use RecursiveIterator;
use Traversable;

class NestIterator extends Dictionary implements RecursiveIterator
{
	/**
	 * The name of the property that contains the nested items for each element
	 *
	 * @var string|callable
	 */
	protected $nestKey;

	/**
	 * Constructor
	 *
	 * @param array|\Traversable $items Collection items.
	 * @param string|callable $nestKey the property that contains the nested items
	 * If a callable is passed, it should return the childrens for the passed item
	 */
	public function __construct($items, $nestKey)
	{
		parent::__construct($items);
		$this->nestKey = $nestKey;
	}

	/**
	 * Returns a traversable containing the children for the current item
	 *
	 * @return \Traversable
	 */
	public function getChildren()
	{
		$property = $this->propertyExtractor($this->nestKey);

		return new static($property($this->current()), $this->nestKey);
	}

	/**
	 * Returns true if there is an array or a traversable object stored under the
	 * configured nestKey for the current item
	 *
	 * @return bool
	 */
	public function hasChildren()
	{
		$property = $this->propertyExtractor($this->nestKey);
		$children = $property($this->current());

		if (is_array($children)) {
			return !empty($children);
		}

		return $children instanceof Traversable;
	}
}