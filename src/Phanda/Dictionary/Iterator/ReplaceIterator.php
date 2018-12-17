<?php

namespace Phanda\Dictionary\Iterator;

use ArrayIterator;
use Phanda\Dictionary\Dictionary;
use Phanda\Contracts\Dictionary\Dictionary as DictionaryContract;

class ReplaceIterator extends Dictionary
{
	/**
	 * The callback function to be used to transform values
	 *
	 * @var callable
	 */
	protected $callback;

	/**
	 * A reference to the internal iterator this object is wrapping.
	 *
	 * @var \Iterator
	 */
	protected $innerIterator;

	/**
	 * Creates an iterator from another iterator that will modify each of the values
	 * by converting them using a callback function.
	 *
	 * Each time the callback is executed it will receive the value of the element
	 * in the current iteration, the key of the element and the passed $items iterator
	 * as arguments, in that order.
	 *
	 * @param array|\Traversable $items The items to be filtered.
	 * @param callable $callback Callback.
	 */
	public function __construct($items, callable $callback)
	{
		$this->callback = $callback;
		parent::__construct($items);
		$this->innerIterator = $this->getInnerIterator();
	}

	/**
	 * Returns the value returned by the callback after passing the current value in
	 * the iteration
	 *
	 * @return mixed
	 */
	public function current()
	{
		$callback = $this->callback;

		return $callback(parent::current(), $this->key(), $this->innerIterator);
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

		$callback = $this->callback;
		$res = [];

		foreach ($iterator as $k => $v) {
			$res[$k] = $callback($v, $k, $iterator);
		}

		return new ArrayIterator($res);
	}
}