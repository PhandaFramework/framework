<?php

namespace Phanda\Dictionary\Iterator;

use Phanda\Dictionary\Util\DictionaryTrait;
use RecursiveIterator;
use RecursiveIteratorIterator;

class TreeIterator extends RecursiveIteratorIterator
{
	use DictionaryTrait;

	/**
	 * The iteration mode
	 *
	 * @var int
	 */
	protected $_mode;

	/**
	 * Constructor
	 *
	 * @param \RecursiveIterator $items The iterator to flatten.
	 * @param int $mode Iterator mode.
	 * @param int $flags Iterator flags.
	 */
	public function __construct(RecursiveIterator $items, $mode = RecursiveIteratorIterator::SELF_FIRST, $flags = 0)
	{
		parent::__construct($items, $mode, $flags);
		$this->_mode = $mode;
	}

	/**
	 * Returns another iterator which will return the values ready to be displayed
	 * to a user. It does so by extracting one property from each of the elements
	 * and prefixing it with a spacer so that the relative position in the tree
	 * can be visualized.
	 *
	 * @param string|callable      $valuePath The property to extract or a callable to return
	 *                                        the display value
	 * @param string|callable|null $keyPath   The property to use as iteration key or a
	 *                                        callable returning the key value.
	 * @param string               $spacer    The string to use for prefixing the values according to
	 *                                        their depth in the tree
	 * @return TreePrinterIterator
	 */
	public function printer($valuePath, $keyPath = null, $spacer = '__')
	{
		if (!$keyPath) {
			$counter = 0;
			$keyPath = function () use (&$counter) {
				return $counter++;
			};
		}

		return new TreePrinterIterator(
			$this->getInnerIterator(),
			$valuePath,
			$keyPath,
			$spacer,
			$this->_mode
		);
	}
}