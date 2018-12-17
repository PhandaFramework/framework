<?php

namespace Phanda\Dictionary\Iterator;

use ArrayIterator;
use Phanda\Dictionary\Dictionary;
use Phanda\Contracts\Dictionary\Dictionary as DictionaryContract;

class StoppableIterator extends Dictionary
{
	/**
	 * The condition to evaluate for each item of the collection
	 *
	 * @var callable
	 */
	protected $condition;

	/**
	 * A reference to the internal iterator this object is wrapping.
	 *
	 * @var \Iterator
	 */
	protected $innerIterator;

	/**
	 * Creates an iterator that can be stopped based on a condition provided by a callback.
	 *
	 * Each time the condition callback is executed it will receive the value of the element
	 * in the current iteration, the key of the element and the passed $items iterator
	 * as arguments, in that order.
	 *
	 * @param array|\Traversable $items The list of values to iterate
	 * @param callable $condition A function that will be called for each item in
	 * the collection, if the result evaluates to false, no more items will be
	 * yielded from this iterator.
	 */
	public function __construct($items, callable $condition)
	{
		$this->condition = $condition;
		parent::__construct($items);
		$this->innerIterator = $this->getInnerIterator();
	}

	/**
	 * Evaluates the condition and returns its result, this controls
	 * whether or not more results will be yielded.
	 *
	 * @return bool
	 */
	public function valid()
	{
		if (!parent::valid()) {
			return false;
		}

		$current = $this->current();
		$key = $this->key();
		$condition = $this->condition;

		return !$condition($current, $key, $this->innerIterator);
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
		$iterator = $this->innerIterator;

		if ($iterator instanceof DictionaryContract) {
			$iterator = $iterator->unwrap();
		}

		if (get_class($iterator) !== ArrayIterator::class) {
			return $this;
		}

		$callback = $this->condition;
		$res = [];

		foreach ($iterator as $k => $v) {
			if ($callback($v, $k, $iterator)) {
				break;
			}
			$res[$k] = $v;
		}

		return new ArrayIterator($res);
	}
}