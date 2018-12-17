<?php

namespace Phanda\Dictionary\Iterator;

use Phanda\Dictionary\Dictionary;
use SplDoublyLinkedList;

class BufferedIterator extends Dictionary implements \Countable, \Serializable
{
	/**
	 * The in-memory cache containing results from previous iterators
	 *
	 * @var \SplDoublyLinkedList
	 */
	protected $internalBuffer;

	/**
	 * Points to the next record number that should be fetched
	 *
	 * @var int
	 */
	protected $internalIndex = 0;

	/**
	 * Last record fetched from the inner iterator
	 *
	 * @var mixed
	 */
	protected $currentRecord;

	/**
	 * Last key obtained from the inner iterator
	 *
	 * @var mixed
	 */
	protected $lastKey;

	/**
	 * Whether or not the internal iterator's rewind method was already
	 * called
	 *
	 * @var bool
	 */
	protected $started = false;

	/**
	 * Whether or not the internal iterator has reached its end.
	 *
	 * @var bool
	 */
	protected $finished = false;

	/**
	 * Maintains an in-memory cache of the results yielded by the internal
	 * iterator.
	 *
	 * @param array|\Traversable $items The items to be filtered.
	 */
	public function __construct($items)
	{
		$this->internalBuffer = new SplDoublyLinkedList();
		parent::__construct($items);
	}

	/**
	 * Returns the current key in the iterator
	 *
	 * @return mixed
	 */
	public function key()
	{
		return $this->lastKey;
	}

	/**
	 * Returns the current record in the iterator
	 *
	 * @return mixed
	 */
	public function current()
	{
		return $this->currentRecord;
	}

	/**
	 * Rewinds the collection
	 *
	 * @return void
	 */
	public function rewind()
	{
		if ($this->internalIndex === 0 && !$this->started) {
			$this->started = true;
			parent::rewind();

			return;
		}

		$this->internalIndex = 0;
	}

	/**
	 * Returns whether or not the iterator has more elements
	 *
	 * @return bool
	 */
	public function valid()
	{
		if ($this->internalBuffer->offsetExists($this->internalIndex)) {
			$current = $this->internalBuffer->offsetGet($this->internalIndex);
			$this->currentRecord = $current['value'];
			$this->lastKey = $current['key'];

			return true;
		}

		$valid = parent::valid();

		if ($valid) {
			$this->currentRecord = parent::current();
			$this->lastKey = parent::key();
			$this->internalBuffer->push([
				'key' => $this->lastKey,
				'value' => $this->currentRecord
			]);
		}

		$this->finished = !$valid;

		return $valid;
	}

	/**
	 * Advances the iterator pointer to the next element
	 *
	 * @return void
	 */
	public function next()
	{
		$this->internalIndex++;

		if (!$this->finished) {
			parent::next();
		}
	}

	/**
	 * Returns the number or items in this collection
	 *
	 * @return int
	 */
	public function count()
	{
		if (!$this->started) {
			$this->rewind();
		}

		while ($this->valid()) {
			$this->next();
		}

		return $this->internalBuffer->count();
	}

	/**
	 * Returns a string representation of this object that can be used
	 * to reconstruct it
	 *
	 * @return string
	 */
	public function serialize()
	{
		if (!$this->finished) {
			$this->count();
		}

		return serialize($this->internalBuffer);
	}

	/**
	 * Unserializes the passed string and rebuilds the BufferedIterator instance
	 *
	 * @param string $buffer The serialized buffer iterator
	 * @return void
	 */
	public function unserialize($buffer)
	{
		$this->__construct([]);
		$this->internalBuffer = unserialize($buffer);
		$this->started = true;
		$this->finished = true;
	}
}