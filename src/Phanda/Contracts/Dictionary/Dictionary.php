<?php

namespace Phanda\Contracts\Dictionary;

use Iterator;
use Phanda\Dictionary\Iterator\BufferedIterator;
use Phanda\Dictionary\Iterator\InsertIterator;
use Phanda\Dictionary\Iterator\ReplaceIterator;
use Phanda\Dictionary\Iterator\SortIterator;
use Phanda\Dictionary\Iterator\StoppableIterator;
use Phanda\Dictionary\Iterator\ZipIterator;

interface Dictionary extends Iterator, \JsonSerializable
{
	/**
	 * Counts the elements in the dictionary.
	 *
	 * @return int
	 */
	public function count();

	/**
	 * {@inheritDoc}
	 */
	public function extract($matcher);

	/**
	 * @inheritdoc
	 */
	public function unwrap();

	/**
	 * @inheritdoc
	 */
	public function filter(callable $c = null);

	/**
	 * Executes the passed callable for each of the elements in this dictionary
	 * and passes both the value and key for them on each step.
	 * Returns the same dictionary for chaining.
	 *
	 * @param callable $c
	 * @return $this
	 */
	public function each(callable $c);

	/**
	 * Looks through each value in the dictionary, and returns another dictionary with
	 * all the values that do not pass a truth test. This is the opposite of `filter`.
	 *
	 * @param callable $c
	 * @return $this
	 */
	public function reject(callable $c);

	/**
	 * Returns true if all values in this dictionary pass the truth test provided
	 * in the callback.
	 *
	 * @param callable $c
	 * @return bool
	 */
	public function every(callable $c): bool;

	/**
	 * Returns true if any of the values in this dictionary pass the truth test
	 * provided in the callback.
	 *
	 * @param callable $c
	 * @return bool
	 */
	public function some(callable $c): bool;

	/**
	 * Returns true if $value is present in this dictionary. Comparisons are made
	 * both by value and type.
	 *
	 * @param mixed
	 * @return bool
	 */
	public function contains($value): bool;

	/**
	 * Returns another dictionary after modifying each of the values in this one using
	 * the provided callable.
	 *
	 * @param callable $c
	 * @return ReplaceIterator
	 */
	public function map(callable $c);

	/**
	 * Returns the top element in this dictionary after being sorted by a property.
	 * Check the sortBy method for information on the callback and $type parameters
	 *
	 * @param callable|string $callback
	 * @param int             $type
	 * @return mixed
	 */
	public function max($callback, $type = \SORT_NUMERIC);

	/**
	 * Returns the bottom element in this dictionary after being sorted by a property.
	 * Check the sortBy method for information on the callback and $type parameters
	 *
	 * @param callable|string $callback
	 * @param int             $type
	 * @return mixed
	 */
	public function min($callback, $type = \SORT_NUMERIC);

	/**
	 * Returns the average of all the values extracted with $matcher
	 * or of this dictionary.
	 *
	 * @param string|callable|null $matcher
	 * @return float|int|null
	 */
	public function avg($matcher = null);

	/**
	 * Returns the median of all the values extracted with $matcher
	 * or of this dictionary.
	 *
	 * @param string|callable|null $matcher
	 * @return float|int|null
	 */
	public function median($matcher = null);

	/**
	 * Returns a sorted iterator out of the elements in this dictionary,
	 * ranked in ascending order by the results of running each value through a
	 * callback. $callback can also be a string representing the column or property
	 * name.
	 *
	 * @param callable|string $callback
	 * @param int             $dir
	 * @param int             $type
	 * @return SortIterator
	 */
	public function sortBy($callback, $dir = \SORT_DESC, $type = \SORT_NUMERIC);

	/**
	 * Splits a dictionary into sets, grouped by the result of running each value
	 * through the callback. If $callback is a string instead of a callable,
	 * groups by the property named by $callback on each of the values.
	 *
	 * @param callable|string $callback
	 * @return $this
	 */
	public function groupBy($callback);

	/**
	 * Given a list and a callback function that returns a key for each element
	 * in the list (or a property name), returns an object with an index of each item.
	 * Just like groupBy, but for when you know your keys are unique.
	 *
	 * @param callable|string $callback
	 * @return $this
	 */
	public function indexBy($callback);

	/**
	 * Sorts a list into groups and returns a count for the number of elements
	 * in each group. Similar to groupBy, but instead of returning a list of values,
	 * returns a count for the number of values in that group.
	 *
	 * @param callable|string $callback
	 * @return $this
	 */
	public function countBy($callback);

	/**
	 * Returns the total sum of all the values extracted with $matcher
	 * or of this dictionary.
	 *
	 * @param string|callable|null $matcher
	 * @return float|int
	 */
	public function sumOf($matcher = null);

	/**
	 * Returns a new dictionary with the elements placed in a random order,
	 * this function does not preserve the original keys in the dictionary.
	 *
	 * @return $this
	 */
	public function shuffle();

	/**
	 * Returns a new dictionary with maximum $size random elements
	 * from this dictionary
	 *
	 * @param int $size
	 * @return $this
	 */
	public function sample($size = 10);

	/**
	 * Returns a new dictionary with maximum $size elements in the internal
	 * order this dictionary was created. If a second parameter is passed, it
	 * will determine from what position to start taking elements.
	 *
	 * @param int $size
	 * @param int $from
	 * @return $this
	 */
	public function take($size = 1, $from = 0);

	/**
	 * Returns the last N elements of a dictionary
	 *
	 * @param int $howMany
	 * @return $this
	 */
	public function takeLast($howMany);

	/**
	 * Returns a new dictionary that will skip the specified amount of elements
	 * at the beginning of the iteration.
	 *
	 * @param int $howMany
	 * @return $this
	 */
	public function skip($howMany);

	/**
	 * Looks through each value in the list, returning a dictionary of all the
	 * values that contain all of the key-value pairs listed in $conditions.
	 *
	 * @param array $conditions
	 * @return $this
	 */
	public function match(array $conditions);

	/**
	 * Returns the first result matching all of the key-value pairs listed in
	 * conditions.
	 *
	 * @param array $conditions
	 * @return mixed
	 */
	public function firstMatch(array $conditions);

	/**
	 * Returns the first result in this dictionary
	 *
	 * @return mixed
	 */
	public function first();

	/**
	 * Returns the last result in this dictionary
	 *
	 * @return mixed
	 */
	public function last();

	/**
	 * Returns a new dictionary as the result of concatenating the list of elements
	 * in this dictionary with the passed list of elements
	 *
	 * @param array|\Traversable $items
	 * @return $this
	 */
	public function append($items);

	/**
	 * @param mixed  $item
	 * @param string $key
	 * @return $this
	 */
	public function appendItem($item, $key = null);

	/**
	 * @param mixed $items
	 * @return $this
	 */
	public function prepend($items);

	/**
	 * @param mixed  $item
	 * @param string $key
	 * @return $this
	 */
	public function prependItem($item, $key = null);

	/**
	 * Returns a new dictionary where the values extracted based on a value path
	 * and then indexed by a key path. Optionally this method can produce parent
	 * groups based on a group property path.
	 *
	 * @param callable|string      $keyPath
	 * @param callable|string      $valuePath
	 * @param callable|string|null $groupPath
	 * @return $this
	 */
	public function combine($keyPath, $valuePath, $groupPath = null);

	/**
	 * Returns a new dictionary where the values are nested in a tree-like structure
	 * based on an id property path and a parent id property path.
	 *
	 * @param callable|string $idPath
	 * @param callable|string $parentPath
	 * @param string          $nestingKey
	 * @return $this
	 */
	public function nest($idPath, $parentPath, $nestingKey = 'children');

	/**
	 * Returns a new dictionary containing each of the elements found in `$values` as
	 * a property inside the corresponding elements in this dictionary. The property
	 * where the values will be inserted is described by the `$path` parameter.
	 * ```
	 *
	 * @param string $path
	 * @param mixed  $values
	 * @return InsertIterator
	 */
	public function insert($path, $values);

	/**
	 * Returns an array representation of the results
	 *
	 * @param bool $preserveKeys
	 * @return array
	 */
	public function toArray($preserveKeys = true): array;

	/**
	 * Returns an numerically-indexed array representation of the results.
	 * This is equivalent to calling `toArray(false)`
	 *
	 * @return array
	 */
	public function toList();

	/**
	 * Returns an array representation of the results
	 *
	 * @return array
	 */
	public function all();

	/**
	 * Convert a result set into JSON.
	 *
	 * Part of JsonSerializable interface.
	 *
	 * @return array The data to convert to JSON
	 */
	public function jsonSerialize();

	/**
	 * Iterates once all elements in this dictionary and executes all stacked
	 * operations of them, finally it returns a new dictionary with the result.
	 * This is useful for converting non-rewindable internal iterators into
	 * a dictionary that can be rewound and used multiple times.
	 *
	 * @param bool $preserveKeys
	 * @return $this
	 */
	public function compile($preserveKeys = true);

	/**
	 * Returns a new dictionary where any operations chained after it are guaranteed
	 * to be run lazily. That is, elements will be yielded one at a time.
	 *
	 * @return $this
	 */
	public function lazy();

	/**
	 * Returns a new dictionary where the operations performed by this dictionary.
	 * No matter how many times the new dictionary is iterated, those operations will
	 * only be performed once.
	 *
	 * @return BufferedIterator
	 */
	public function buffered();

	/**
	 * Returns a new dictionary with each of the elements of this dictionary
	 * after flattening the tree structure. The tree structure is defined
	 * by nesting elements under a key with a known name. It is possible
	 * to specify such name by using the '$nestingKey' parameter.
	 *
	 * @param string|int      $dir
	 * @param string|callable $nestingKey
	 * @return $this
	 */
	public function listNested($dir = 'desc', $nestingKey = 'children');

	/**
	 * Creates a new dictionary that when iterated will stop yielding results if
	 * the provided condition evaluates to false.
	 *
	 * @param callable|array $condition
	 * @return StoppableIterator
	 */
	public function stopWhen($condition);

	/**
	 * Creates a new dictionary where the items are the
	 * concatenation of the lists of items generated by the transformer function
	 * applied to each item in the original dictionary.
	 *
	 * @param callable|null $transformer
	 * @return $this
	 */
	public function unfold(callable $transformer = null);

	/**
	 * Passes this dictionary through a callable as its first argument.
	 * This is useful for decorating the full dictionary with another object.
	 *
	 * @param callable $handler
	 * @return $this
	 */
	public function through(callable $handler);

	/**
	 * Combines the elements of this dictionary with each of the elements of the
	 * passed iterables, using their positional index as a reference.
	 *
	 * @param array|\Traversable ...$items
	 * @return ZipIterator
	 */
	public function zip($items);

	/**
	 * Combines the elements of this dictionary with each of the elements of the
	 * passed iterables, using their positional index as a reference.
	 *
	 * @param array|\Traversable ...$items
	 * @param callable           $callable
	 * @return ZipIterator
	 */
	public function zipWith($items, $callable);

	/**
	 * Breaks the dictionary into smaller arrays of the given size.
	 *
	 * @param int $chunkSize
	 * @return $this
	 */
	public function chunk($chunkSize);

	/**
	 * Breaks the dictionary into smaller arrays of the given size.
	 *
	 * @param int  $chunkSize
	 * @param bool $preserveKeys
	 * @return $this
	 */
	public function chunkWithKeys($chunkSize, $preserveKeys = true);

	/**
	 * Returns whether or not there are elements in this dictionary
	 *
	 * @return bool
	 */
	public function isEmpty(): bool;

	/**
	 * Performs a cartesian product.
	 *
	 * @param callable|null $operation
	 * @param callable|null $filter
	 * @return $this
	 */
	public function cartesianProduct(?callable $operation = null, ?callable $filter = null);

	/**
	 * Transpose rows and columns into columns and rows
	 *
	 * @return $this
	 */
	public function transpose();

	/**
	 * Returns the number of unique keys in this iterator. This is, the number of
	 * elements the dictionary will contain after calling `toArray()`
	 *
	 * @return int
	 */
	public function countKeys();
}