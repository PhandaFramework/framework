<?php

namespace Phanda\Dictionary\Iterator;

use Phanda\Dictionary\Util\DictionaryTrait;
use RecursiveIteratorIterator;

class TreePrinterIterator extends RecursiveIteratorIterator
{
	use DictionaryTrait;

	/**
	 * A callable to generate the iteration key
	 *
	 * @var callable
	 */
	protected $key;

	/**
	 * A callable to extract the display value
	 *
	 * @var callable
	 */
	protected $value;

	/**
	 * Cached value for the current iteration element
	 *
	 * @var mixed
	 */
	protected $current;

	/**
	 * The string to use for prefixing the values according to their depth in the tree.
	 *
	 * @var string
	 */
	protected $spacer;

	/**
	 * Constructor
	 *
	 * @param \RecursiveIterator|\Iterator $items     The iterator to flatten.
	 * @param string|callable              $valuePath The property to extract or a callable to return
	 *                                                the display value.
	 * @param string|callable              $keyPath   The property to use as iteration key or a
	 *                                                callable returning the key value.
	 * @param string                       $spacer    The string to use for prefixing the values according to
	 *                                                their depth in the tree.
	 * @param int                          $mode      Iterator mode.
	 */
	public function __construct($items, $valuePath, $keyPath, $spacer, $mode = RecursiveIteratorIterator::SELF_FIRST)
	{
		parent::__construct($items, $mode);
		$this->value = $this->propertyExtractor($valuePath);
		$this->key = $this->propertyExtractor($keyPath);
		$this->spacer = $spacer;
	}

	/**
	 * Returns the current iteration key
	 *
	 * @return mixed
	 */
	public function key()
	{
		$extractor = $this->key;

		return $extractor($this->_fetchCurrent(), parent::key(), $this);
	}

	/**
	 * Returns the current iteration value
	 *
	 * @return string
	 */
	public function current()
	{
		$extractor = $this->value;
		$current = $this->_fetchCurrent();
		$spacer = str_repeat($this->spacer, $this->getDepth());

		return $spacer . $extractor($current, parent::key(), $this);
	}

	/**
	 * Advances the cursor one position
	 *
	 * @return void
	 */
	public function next()
	{
		parent::next();
		$this->current = null;
	}

	/**
	 * Returns the current iteration element and caches its value
	 *
	 * @return mixed
	 */
	protected function _fetchCurrent()
	{
		if ($this->current !== null) {
			return $this->current;
		}

		return $this->current = parent::current();
	}
}