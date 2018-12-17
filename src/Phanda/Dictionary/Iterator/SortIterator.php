<?php

namespace Phanda\Dictionary\Iterator;

use DateTimeInterface;
use Phanda\Dictionary\Dictionary;

class SortIterator extends Dictionary
{
	/**
	 * Wraps this iterator around the passed items so when iterated they are returned
	 * in order.
	 *
	 * @param array|\Traversable $items
	 * @param callable|string    $callback
	 * @param int                $dir
	 * @param int                $type
	 */
	public function __construct($items, $callback, int $dir = \SORT_DESC, int $type = \SORT_NUMERIC)
	{
		if (!is_array($items)) {
			$items = iterator_to_array((new Dictionary($items))->unwrap(), false);
		}

		$callback = $this->propertyExtractor($callback);
		$results = [];
		foreach ($items as $key => $val) {
			$val = $callback($val);
			if ($val instanceof DateTimeInterface && $type === \SORT_NUMERIC) {
				$val = $val->format('U');
			}
			$results[$key] = $val;
		}

		$dir === SORT_DESC ? arsort($results, $type) : asort($results, $type);

		foreach (array_keys($results) as $key) {
			$results[$key] = $items[$key];
		}
		parent::__construct($results);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return \Iterator
	 */
	public function unwrap()
	{
		return $this->getInnerIterator();
	}
}