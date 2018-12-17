<?php

namespace Phanda\Dictionary\Iterator;

use MultipleIterator;
use Phanda\Dictionary\Util\DictionaryTrait;
use Phanda\Dictionary\Dictionary;
use Phanda\Contracts\Dictionary\Dictionary as DictionaryContract;
use Serializable;

class ZipIterator extends MultipleIterator implements DictionaryContract, Serializable
{
	use DictionaryTrait;

	/**
	 * The function to use for zipping items together
	 *
	 * @var callable
	 */
	protected $cb;

	/**
	 * Contains the original iterator objects that were attached
	 *
	 * @var array
	 */
	protected $iterators = [];

	/**
	 * Creates the iterator to merge together the values by for all the passed
	 * iterators by their corresponding index.
	 *
	 * @param array $sets The list of array or iterators to be zipped.
	 * @param callable|null $callable The function to use for zipping the elements of each iterator.
	 */
	public function __construct(array $sets, $callable = null)
	{
		$sets = array_map(function ($items) {
			return (new Dictionary($items))->unwrap();
		}, $sets);

		$this->cb = $callable;
		parent::__construct(MultipleIterator::MIT_NEED_ALL | MultipleIterator::MIT_KEYS_NUMERIC);

		foreach ($sets as $set) {
			$this->iterators[] = $set;
			$this->attachIterator($set);
		}
	}

	/**
	 * Returns the value resulting out of zipping all the elements for all the
	 * iterators with the same positional index.
	 *
	 * @return mixed
	 */
	public function current()
	{
		if ($this->cb === null) {
			return parent::current();
		}

		return call_user_func_array($this->cb, parent::current());
	}

	/**
	 * Returns a string representation of this object that can be used
	 * to reconstruct it
	 *
	 * @return string
	 */
	public function serialize()
	{
		return serialize($this->iterators);
	}

	/**
	 * Unserializes the passed string and rebuilds the ZipIterator instance
	 *
	 * @param string $iterators The serialized iterators
	 * @return void
	 */
	public function unserialize($iterators)
	{
		parent::__construct(MultipleIterator::MIT_NEED_ALL | MultipleIterator::MIT_KEYS_NUMERIC);
		$this->iterators = unserialize($iterators);
		foreach ($this->iterators as $it) {
			$this->attachIterator($it);
		}
	}
}