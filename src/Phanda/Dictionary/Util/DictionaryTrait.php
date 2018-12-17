<?php

namespace Phanda\Dictionary\Util;

use AppendIterator;
use ArrayIterator;
use Countable;
use LimitIterator;
use LogicException;
use Phanda\Dictionary\Dictionary;
use Phanda\Contracts\Dictionary\Dictionary as DictionaryContract;
use Phanda\Dictionary\Iterator\BufferedIterator;
use Phanda\Dictionary\Iterator\ExtractIterator;
use Phanda\Dictionary\Iterator\FilterIterator;
use Phanda\Dictionary\Iterator\InsertIterator;
use Phanda\Dictionary\Iterator\MapReduceIterator;
use Phanda\Dictionary\Iterator\NestIterator;
use Phanda\Dictionary\Iterator\ReplaceIterator;
use Phanda\Dictionary\Iterator\SortIterator;
use Phanda\Dictionary\Iterator\StoppableIterator;
use Phanda\Dictionary\Iterator\TreeIterator;
use Phanda\Dictionary\Iterator\UnfoldIterator;
use Phanda\Dictionary\Iterator\ZipIterator;
use RecursiveIteratorIterator;
use Traversable;

/**
 * Trait ResultSetTrait
 *
 * @package Phanda\Bear\Util\Results
 *
 * @mixin Dictionary
 */
trait DictionaryTrait
{

	/**
	 * Executes the passed callable for each of the elements in this dictionary
	 * and passes both the value and key for them on each step.
	 * Returns the same dictionary for chaining.
	 *
	 * @param callable $c
	 * @return DictionaryContract
	 */
	public function each(callable $c)
	{
		foreach ($this->optimizeUnwrap() as $k => $v) {
			$c($v, $k);
		}

		return $this;
	}

	/**
	 * Unwraps this iterator and returns the simplest
	 * traversable that can be used for getting the data out
	 *
	 * @return \Traversable|array
	 */
	protected function optimizeUnwrap()
	{
		$iterator = $this->unwrap();

		if (get_class($iterator) === ArrayIterator::class) {
			$iterator = $iterator->getArrayCopy();
		}

		return $iterator;
	}

	/**
	 * Returns the closest nested iterator that can be safely traversed without
	 * losing any possible transformations. This is used mainly to remove empty
	 * IteratorIterator wrappers that can only slowdown the iteration process.
	 *
	 * @return \Traversable
	 */
	public function unwrap()
	{
		$iterator = $this;
		while (get_class($iterator) === Dictionary::class) {
			$iterator = $iterator->getInnerIterator();
		}

		if ($iterator !== $this && $iterator instanceof DictionaryContract) {
			$iterator = $iterator->unwrap();
		}

		return $iterator;
	}

	/**
	 * Looks through each value in the dictionary, and returns another dictionary with
	 * all the values that do not pass a truth test. This is the opposite of `filter`.
	 *
	 * @param callable $c
	 * @return DictionaryContract
	 */
	public function reject(callable $c)
	{
		return new FilterIterator($this->unwrap(), function ($key, $value, $items) use ($c) {
			return !$c($key, $value, $items);
		});
	}

	/**
	 * Returns true if all values in this dictionary pass the truth test provided
	 * in the callback.
	 *
	 * @param callable $c
	 * @return bool
	 */
	public function every(callable $c): bool
	{
		foreach ($this->optimizeUnwrap() as $key => $value) {
			if (!$c($value, $key)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns true if any of the values in this dictionary pass the truth test
	 * provided in the callback.
	 *
	 * @param callable $c
	 * @return bool
	 */
	public function some(callable $c): bool
	{
		foreach ($this->optimizeUnwrap() as $key => $value) {
			if ($c($value, $key) === true) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true if $value is present in this dictionary. Comparisons are made
	 * both by value and type.
	 *
	 * @param mixed
	 * @return bool
	 */
	public function contains($value): bool
	{
		foreach ($this->optimizeUnwrap() as $v) {
			if ($value === $v) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the top element in this dictionary after being sorted by a property.
	 * Check the sortBy method for information on the callback and $type parameters
	 *
	 * @param callable|string $callback
	 * @param int             $type
	 * @return mixed
	 */
	public function max($callback, $type = \SORT_NUMERIC)
	{
		return (new SortIterator($this->unwrap(), $callback, \SORT_DESC, $type))->first();
	}

	/**
	 * Returns the first result in this dictionary
	 *
	 * @return mixed
	 */
	public function first()
	{
		$iterator = new LimitIterator($this, 0, 1);
		foreach ($iterator as $result) {
			return $result;
		}
	}

	/**
	 * Returns the bottom element in this dictionary after being sorted by a property.
	 * Check the sortBy method for information on the callback and $type parameters
	 *
	 * @param callable|string $callback
	 * @param int             $type
	 * @return mixed
	 */
	public function min($callback, $type = \SORT_NUMERIC)
	{
		return (new SortIterator($this->unwrap(), $callback, \SORT_ASC, $type))->first();
	}

	/**
	 * Returns the average of all the values extracted with $matcher
	 * or of this dictionary.
	 *
	 * @param string|callable|null $matcher
	 * @return float|int|null
	 */
	public function avg($matcher = null)
	{
		$result = $this;
		if ($matcher != null) {
			$result = $result->extract($matcher);
		}
		$result = $result
			->reduce(function ($acc, $current) {
				list($count, $sum) = $acc;

				return [$count + 1, $sum + $current];
			}, [0, 0]);

		if ($result[0] === 0) {
			return null;
		}

		return $result[1] / $result[0];
	}

	/**
	 * {@inheritDoc}
	 */
	public function extract($matcher)
	{
		$extractor = new ExtractIterator($this->unwrap(), $matcher);
		if (is_string($matcher) && strpos($matcher, '{*}') !== false) {
			$extractor = $extractor
				->filter(function ($data) {
					return $data !== null && ($data instanceof Traversable || is_array($data));
				})
				->unfold();
		}

		return $extractor;
	}

	/**
	 * Creates a new dictionary where the items are the
	 * concatenation of the lists of items generated by the transformer function
	 * applied to each item in the original dictionary.
	 *
	 * @param callable|null $transformer
	 * @return DictionaryContract
	 */
	public function unfold(callable $transformer = null)
	{
		if ($transformer === null) {
			$transformer = function ($item) {
				return $item;
			};
		}

		return new Dictionary(
			new RecursiveIteratorIterator(
				new UnfoldIterator($this->unwrap(), $transformer),
				RecursiveIteratorIterator::LEAVES_ONLY
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function filter(callable $c = null)
	{
		if ($c === null) {
			$c = function ($v) {
				return (bool)$v;
			};
		}

		return new FilterIterator($this->unwrap(), $c);
	}

	/**
	 * Returns the median of all the values extracted with $matcher
	 * or of this dictionary.
	 *
	 * @param string|callable|null $matcher
	 * @return float|int|null
	 */
	public function median($matcher = null)
	{
		$elements = $this;
		if ($matcher != null) {
			$elements = $elements->extract($matcher);
		}
		$values = $elements->toList();
		sort($values);
		$count = count($values);

		if ($count === 0) {
			return null;
		}

		$middle = (int)($count / 2);

		if ($count % 2) {
			return $values[$middle];
		}

		return ($values[$middle - 1] + $values[$middle]) / 2;
	}

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
	public function sortBy($callback, $dir = \SORT_DESC, $type = \SORT_NUMERIC)
	{
		return new SortIterator($this->unwrap(), $callback, $dir, $type);
	}

	/**
	 * Splits a dictionary into sets, grouped by the result of running each value
	 * through the callback. If $callback is a string instead of a callable,
	 * groups by the property named by $callback on each of the values.
	 *
	 * @param callable|string $callback
	 * @return DictionaryContract
	 */
	public function groupBy($callback)
	{
		$callback = $this->propertyExtractor($callback);
		$group = [];
		foreach ($this->optimizeUnwrap() as $value) {
			$group[$callback($value)][] = $value;
		}

		return new Dictionary($group);
	}

	/**
	 * Returns a callable that can be used to extract a property or column from
	 * an array or object based on a dot separated path.
	 *
	 * @param string|callable $callback
	 * @return callable
	 */
	protected function propertyExtractor($callback): callable
	{
		if (!is_string($callback)) {
			return $callback;
		}

		$path = explode('.', $callback);

		if (strpos($callback, '{*}') !== false) {
			return function ($element) use ($path) {
				return $this->_extract($element, $path);
			};
		}

		return function ($element) use ($path) {
			return $this->_simpleExtract($element, $path);
		};
	}

	/**
	 * Returns a column from $data that can be extracted
	 * by iterating over the column names contained in $path.
	 *
	 * @param array|\ArrayAccess $data Data.
	 * @param array              $path Path to extract from.
	 * @return mixed
	 */
	protected function _extract($data, array $path)
	{
		$value = null;
		$dictionaryTransform = false;

		foreach ($path as $i => $column) {
			if ($column === '{*}') {
				$dictionaryTransform = true;
				continue;
			}

			if ($dictionaryTransform &&
				!($data instanceof Traversable || is_array($data))) {
				return null;
			}

			if ($dictionaryTransform) {
				$rest = implode('.', array_slice($path, $i));

				return (new Dictionary($data))->extract($rest);
			}

			if (!isset($data[$column])) {
				return null;
			}

			$value = $data[$column];
			$data = $value;
		}

		return $value;
	}

	/**
	 * Returns a column from $data that can be extracted
	 * by iterating over the column names contained in $path
	 *
	 * @param array|\ArrayAccess $data Data.
	 * @param array              $path Path to extract from.
	 * @return mixed
	 */
	protected function _simpleExtract($data, $path)
	{
		$value = null;
		foreach ($path as $column) {
			if (!isset($data[$column])) {
				return null;
			}
			$value = $data[$column];
			$data = $value;
		}

		return $value;
	}

	/**
	 * Given a list and a callback function that returns a key for each element
	 * in the list (or a property name), returns an object with an index of each item.
	 * Just like groupBy, but for when you know your keys are unique.
	 *
	 * @param callable|string $callback
	 * @return DictionaryContract
	 */
	public function indexBy($callback)
	{
		$callback = $this->propertyExtractor($callback);
		$group = [];
		foreach ($this->optimizeUnwrap() as $value) {
			$group[$callback($value)] = $value;
		}

		return new Dictionary($group);
	}

	/**
	 * Sorts a list into groups and returns a count for the number of elements
	 * in each group. Similar to groupBy, but instead of returning a list of values,
	 * returns a count for the number of values in that group.
	 *
	 * @param callable|string $callback
	 * @return DictionaryContract
	 */
	public function countBy($callback)
	{
		$callback = $this->propertyExtractor($callback);

		$mapper = function ($value, $key, $mr) use ($callback) {
			/** @var MapReduceIterator $mr */
			$mr->appendIntermediate($value, $callback($value));
		};

		$reducer = function ($values, $key, $mr) {
			/** @var MapReduceIterator $mr */
			$mr->append(count($values), $key);
		};

		return new Dictionary(new MapReduceIterator($this->unwrap(), $mapper, $reducer));
	}

	/**
	 * Returns the total sum of all the values extracted with $matcher
	 * or of this dictionary.
	 *
	 * @param string|callable|null $matcher
	 * @return float|int
	 */
	public function sumOf($matcher = null)
	{
		if ($matcher === null) {
			return array_sum($this->toList());
		}

		$callback = $this->propertyExtractor($matcher);
		$sum = 0;
		foreach ($this->optimizeUnwrap() as $k => $v) {
			$sum += $callback($v, $k);
		}

		return $sum;
	}

	/**
	 * Returns an numerically-indexed array representation of the results.
	 * This is equivalent to calling `toArray(false)`
	 *
	 * @return array
	 */
	public function toList()
	{
		return $this->toArray(false);
	}

	/**
	 * Returns an array representation of the results
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->toArray();
	}

	/**
	 * Returns an array representation of the results
	 *
	 * @param bool $preserveKeys
	 * @return array
	 */
	public function toArray($preserveKeys = true): array
	{
		$iterator = $this->unwrap();
		if ($iterator instanceof ArrayIterator) {
			$items = $iterator->getArrayCopy();

			return $preserveKeys ? $items : array_values($items);
		}

		if ($preserveKeys && get_class($iterator) === \RecursiveIteratorIterator::class) {
			$preserveKeys = false;
		}

		return iterator_to_array($this, $preserveKeys);
	}

	/**
	 * Returns a new dictionary with maximum $size random elements
	 * from this dictionary
	 *
	 * @param int $size
	 * @return DictionaryContract
	 */
	public function sample($size = 10)
	{
		return new Dictionary(new LimitIterator($this->shuffle(), 0, $size));
	}

	/**
	 * Returns a new dictionary with the elements placed in a random order,
	 * this function does not preserve the original keys in the dictionary.
	 *
	 * @return DictionaryContract
	 */
	public function shuffle()
	{
		$elements = $this->toArray();
		shuffle($elements);

		return new Dictionary($elements);
	}

	/**
	 * Returns a new dictionary with maximum $size elements in the internal
	 * order this dictionary was created. If a second parameter is passed, it
	 * will determine from what position to start taking elements.
	 *
	 * @param int $size
	 * @param int $from
	 * @return DictionaryContract
	 */
	public function take($size = 1, $from = 0)
	{
		return new Dictionary(new LimitIterator($this, $from, $size));
	}

	/**
	 * Returns the last N elements of a dictionary
	 *
	 * @param int $howMany
	 * @return DictionaryContract
	 */
	public function takeLast($howMany)
	{
		if ($howMany < 1) {
			throw new \InvalidArgumentException("The takeLast method requires a number greater than 0.");
		}

		$iterator = $this->optimizeUnwrap();
		if (is_array($iterator)) {
			return new Dictionary(array_slice($iterator, $howMany * -1));
		}

		if ($iterator instanceof Countable) {
			$count = count($iterator);

			if ($count === 0) {
				return new Dictionary([]);
			}

			$iterator = new LimitIterator($iterator, max(0, $count - $howMany), $howMany);

			return new Dictionary($iterator);
		}

		$generator = function ($iterator, $howMany) {
			$result = [];
			$bucket = 0;
			$offset = 0;

			foreach ($iterator as $k => $item) {
				$result[$bucket] = [$k, $item];
				$bucket = (++$bucket) % $howMany;
				$offset++;
			}

			$offset = $offset % $howMany;
			$head = array_slice($result, $offset);
			$tail = array_slice($result, 0, $offset);

			foreach ($head as $v) {
				yield $v[0] => $v[1];
			}

			foreach ($tail as $v) {
				yield $v[0] => $v[1];
			}
		};

		return new Dictionary($generator($iterator, $howMany));
	}

	/**
	 * Returns a new dictionary that will skip the specified amount of elements
	 * at the beginning of the iteration.
	 *
	 * @param int $howMany
	 * @return DictionaryContract
	 */
	public function skip($howMany)
	{
		return new Dictionary(new LimitIterator($this, $howMany));
	}

	/**
	 * Returns the first result matching all of the key-value pairs listed in
	 * conditions.
	 *
	 * @param array $conditions
	 * @return mixed
	 */
	public function firstMatch(array $conditions)
	{
		return $this->match($conditions)->first();
	}

	/**
	 * Looks through each value in the list, returning a dictionary of all the
	 * values that contain all of the key-value pairs listed in $conditions.
	 *
	 * @param array $conditions
	 * @return DictionaryContract
	 */
	public function match(array $conditions)
	{
		return $this->filter($this->createMatcherFilter($conditions));
	}

	/**
	 * Returns a callable that receives a value and will return whether or not
	 * it matches certain condition.
	 *
	 * @param array $conditions
	 * @return callable
	 */
	protected function createMatcherFilter(array $conditions): callable
	{
		$matchers = [];
		foreach ($conditions as $property => $value) {
			$extractor = $this->propertyExtractor($property);
			$matchers[] = function ($v) use ($extractor, $value) {
				return $extractor($v) == $value;
			};
		}

		return function ($value) use ($matchers) {
			foreach ($matchers as $match) {
				if (!$match($value)) {
					return false;
				}
			}

			return true;
		};
	}

	/**
	 * Returns the last result in this dictionary
	 *
	 * @return mixed
	 */
	public function last()
	{
		$iterator = $this->optimizeUnwrap();
		if (is_array($iterator)) {
			return array_pop($iterator);
		}

		if ($iterator instanceof Countable) {
			$count = count($iterator);
			if ($count === 0) {
				return null;
			}
		}

		$result = null;
		return $result;
	}

	public function appendItem($item, $key = null)
	{
		if ($key !== null) {
			$data = [$key => $item];
		} else {
			$data = [$item];
		}

		return $this->append($data);
	}

	/**
	 * Returns a new dictionary as the result of concatenating the list of elements
	 * in this dictionary with the passed list of elements
	 *
	 * @param array|\Traversable $items
	 * @return DictionaryContract
	 */
	public function append($items)
	{
		$list = new AppendIterator();
		$list->append($this->unwrap());
		$list->append((new Dictionary($items))->unwrap());

		return new Dictionary($list);
	}

	public function prependItem($item, $key = null)
	{
		if ($key !== null) {
			$data = [$key => $item];
		} else {
			$data = [$item];
		}

		return $this->prepend($data);
	}

	public function prepend($items)
	{
		return (new Dictionary($items))->append($this);
	}

	/**
	 * Returns a new dictionary where the values extracted based on a value path
	 * and then indexed by a key path. Optionally this method can produce parent
	 * groups based on a group property path.
	 *
	 * @param callable|string      $keyPath
	 * @param callable|string      $valuePath
	 * @param callable|string|null $groupPath
	 * @return DictionaryContract
	 */
	public function combine($keyPath, $valuePath, $groupPath = null)
	{
		$options = [
			'keyPath' => $this->propertyExtractor($keyPath),
			'valuePath' => $this->propertyExtractor($valuePath),
			'groupPath' => $groupPath ? $this->propertyExtractor($groupPath) : null
		];

		$mapper = function ($value, $key, $mapReduce) use ($options) {
			/** @var MapReduceIterator $mapReduce */
			$rowKey = $options['keyPath'];
			$rowVal = $options['valuePath'];

			if (!$options['groupPath']) {
				$mapReduce->append($rowVal($value, $key), $rowKey($value, $key));

				return null;
			}

			$key = $options['groupPath']($value, $key);
			$mapReduce->appendIntermediate(
				[$rowKey($value, $key) => $rowVal($value, $key)],
				$key
			);
		};

		$reducer = function ($values, $key, $mapReduce) {
			/** @var MapReduceIterator $mapReduce */
			$result = [];
			foreach ($values as $value) {
				$result += $value;
			}
			$mapReduce->append($result, $key);
		};

		return new Dictionary(new MapReduceIterator($this->unwrap(), $mapper, $reducer));
	}

	/**
	 * Returns a new dictionary where the values are nested in a tree-like structure
	 * based on an id property path and a parent id property path.
	 *
	 * @param callable|string $idPath
	 * @param callable|string $parentPath
	 * @param string          $nestingKey
	 * @return DictionaryContract
	 */
	public function nest($idPath, $parentPath, $nestingKey = 'children')
	{
		$parents = [];
		$idPath = $this->propertyExtractor($idPath);
		$parentPath = $this->propertyExtractor($parentPath);
		$isObject = true;

		$mapper = function ($row, $key, $mapReduce) use (&$parents, $idPath, $parentPath, $nestingKey) {
			/** @var MapReduceIterator $mapReduce */
			$row[$nestingKey] = [];
			$id = $idPath($row, $key);
			$parentId = $parentPath($row, $key);
			$parents[$id] =& $row;
			$mapReduce->appendIntermediate($id, $parentId);
		};

		$reducer = function ($values, $key, $mapReduce) use (&$parents, &$isObject, $nestingKey) {
			/** @var MapReduceIterator $mapReduce */
			static $foundOutType = false;
			if (!$foundOutType) {
				$isObject = is_object(current($parents));
				$foundOutType = true;
			}
			if (empty($key) || !isset($parents[$key])) {
				foreach ($values as $id) {
					$parents[$id] = $isObject ? $parents[$id] : new ArrayIterator($parents[$id], 1);
					$mapReduce->append($parents[$id]);
				}

				return null;
			}

			$children = [];
			foreach ($values as $id) {
				$children[] =& $parents[$id];
			}
			$parents[$key][$nestingKey] = $children;
		};

		return (new Dictionary(new MapReduceIterator($this->unwrap(), $mapper, $reducer)))
			->map(function ($value) use (&$isObject) {
				/** @var mixed $value */
				return $isObject ? $value : $value->getArrayCopy();
			});
	}

	/**
	 * Returns another dictionary after modifying each of the values in this one using
	 * the provided callable.
	 *
	 * @param callable $c
	 * @return ReplaceIterator
	 */
	public function map(callable $c)
	{
		return new ReplaceIterator($this->unwrap(), $c);
	}

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
	public function insert($path, $values)
	{
		return new InsertIterator($this->unwrap(), $path, $values);
	}

	/**
	 * Convert a result set into JSON.
	 *
	 * Part of JsonSerializable interface.
	 *
	 * @return array The data to convert to JSON
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}

	/**
	 * Iterates once all elements in this dictionary and executes all stacked
	 * operations of them, finally it returns a new dictionary with the result.
	 * This is useful for converting non-rewindable internal iterators into
	 * a dictionary that can be rewound and used multiple times.
	 *
	 * @param bool $preserveKeys
	 * @return DictionaryContract
	 */
	public function compile($preserveKeys = true)
	{
		return new Dictionary($this->toArray($preserveKeys));
	}

	/**
	 * Returns a new dictionary where any operations chained after it are guaranteed
	 * to be run lazily. That is, elements will be yielded one at a time.
	 *
	 * @return DictionaryContract
	 */
	public function lazy()
	{
		$generator = function () {
			foreach ($this->unwrap() as $k => $v) {
				yield $k => $v;
			}
		};

		return new Dictionary($generator());
	}

	/**
	 * Returns a new dictionary where the operations performed by this dictionary.
	 * No matter how many times the new dictionary is iterated, those operations will
	 * only be performed once.
	 *
	 * @return BufferedIterator
	 */
	public function buffered()
	{
		return new BufferedIterator($this->unwrap());
	}

	/**
	 * Returns a new dictionary with each of the elements of this dictionary
	 * after flattening the tree structure. The tree structure is defined
	 * by nesting elements under a key with a known name. It is possible
	 * to specify such name by using the '$nestingKey' parameter.
	 *
	 * @param string|int      $dir
	 * @param string|callable $nestingKey
	 * @return DictionaryContract
	 */
	public function listNested($dir = 'desc', $nestingKey = 'children')
	{
		$dir = strtolower($dir);
		$modes = [
			'desc' => TreeIterator::SELF_FIRST,
			'asc' => TreeIterator::CHILD_FIRST,
			'leaves' => TreeIterator::LEAVES_ONLY
		];

		return new TreeIterator(
			new NestIterator($this, $nestingKey),
			isset($modes[$dir]) ? $modes[$dir] : $dir
		);
	}

	/**
	 * Creates a new dictionary that when iterated will stop yielding results if
	 * the provided condition evaluates to false.
	 *
	 * @param callable|array $condition
	 * @return StoppableIterator
	 */
	public function stopWhen($condition)
	{
		if (!is_callable($condition)) {
			$condition = $this->createMatcherFilter($condition);
		}

		return new StoppableIterator($this->unwrap(), $condition);
	}

	/**
	 * Passes this dictionary through a callable as its first argument.
	 * This is useful for decorating the full dictionary with another object.
	 *
	 * @param callable $handler
	 * @return DictionaryContract
	 */
	public function through(callable $handler)
	{
		$result = $handler($this);

		return $result instanceof DictionaryContract ? $result : new Dictionary($result);
	}

	/**
	 * Combines the elements of this dictionary with each of the elements of the
	 * passed iterables, using their positional index as a reference.
	 *
	 * @param array|\Traversable ...$items
	 * @return ZipIterator
	 */
	public function zip($items)
	{
		return new ZipIterator(array_merge([$this->unwrap()], func_get_args()));
	}

	/**
	 * Combines the elements of this dictionary with each of the elements of the
	 * passed iterables, using their positional index as a reference.
	 *
	 * @param array|\Traversable ...$items
	 * @param callable           $callable
	 * @return ZipIterator
	 */
	public function zipWith($items, $callable)
	{
		if (func_num_args() > 2) {
			$items = func_get_args();
			$callable = array_pop($items);
		} else {
			$items = [$items];
		}

		return new ZipIterator(array_merge([$this->unwrap()], $items), $callable);
	}

	/**
	 * Breaks the dictionary into smaller arrays of the given size.
	 *
	 * @param int $chunkSize
	 * @return DictionaryContract
	 */
	public function chunk($chunkSize)
	{
		return $this->map(function ($v, $k, $iterator) use ($chunkSize) {
			/** @var \Iterator $iterator */
			$values = [$v];
			for ($i = 1; $i < $chunkSize; $i++) {
				$iterator->next();
				if (!$iterator->valid()) {
					break;
				}
				$values[] = $iterator->current();
			}

			return $values;
		});
	}

	/**
	 * Breaks the dictionary into smaller arrays of the given size.
	 *
	 * @param int  $chunkSize
	 * @param bool $preserveKeys
	 * @return DictionaryContract
	 */
	public function chunkWithKeys($chunkSize, $preserveKeys = true)
	{
		return $this->map(function ($v, $k, $iterator) use ($chunkSize, $preserveKeys) {
			/** @var \Iterator $iterator */
			$key = 0;
			if ($preserveKeys) {
				$key = $k;
			}
			$values = [$key => $v];
			for ($i = 1; $i < $chunkSize; $i++) {
				$iterator->next();
				if (!$iterator->valid()) {
					break;
				}
				if ($preserveKeys) {
					$values[$iterator->key()] = $iterator->current();
				} else {
					$values[] = $iterator->current();
				}
			}

			return $values;
		});
	}

	/**
	 * Performs a cartesian product.
	 *
	 * @param callable|null $operation
	 * @param callable|null $filter
	 * @return DictionaryContract
	 */
	public function cartesianProduct(?callable $operation = null, ?callable $filter = null)
	{
		if ($this->isEmpty()) {
			return new Dictionary([]);
		}

		$dictionaryArrays = [];
		$dictionaryArraysKeys = [];
		$dictionaryArraysCounts = [];

		foreach ($this->toList() as $value) {
			$valueCount = count($value);
			if ($valueCount !== count($value, COUNT_RECURSIVE)) {
				throw new LogicException('Cannot find the cartesian product of a multidimensional array');
			}

			$dictionaryArraysKeys[] = array_keys($value);
			$dictionaryArraysCounts[] = $valueCount;
			$dictionaryArrays[] = $value;
		}

		$result = [];
		$lastIndex = count($dictionaryArrays) - 1;
		// holds the indexes of the arrays that generate the current combination
		$currentIndexes = array_fill(0, $lastIndex + 1, 0);

		$changeIndex = $lastIndex;

		while (!($changeIndex === 0 && $currentIndexes[0] === $dictionaryArraysCounts[0])) {
			$currentCombination = array_map(function ($value, $keys, $index) {
				return $value[$keys[$index]];
			}, $dictionaryArrays, $dictionaryArraysKeys, $currentIndexes);

			if ($filter === null || $filter($currentCombination)) {
				$result[] = ($operation === null) ? $currentCombination : $operation($currentCombination);
			}

			$currentIndexes[$lastIndex]++;

			for ($changeIndex = $lastIndex; $currentIndexes[$changeIndex] === $dictionaryArraysCounts[$changeIndex] && $changeIndex > 0; $changeIndex--) {
				$currentIndexes[$changeIndex] = 0;
				$currentIndexes[$changeIndex - 1]++;
			}
		}

		return new Dictionary($result);
	}

	/**
	 * Returns whether or not there are elements in this dictionary
	 *
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		foreach ($this as $el) {
			return false;
		}

		return true;
	}

	/**
	 * Transpose rows and columns into columns and rows
	 *
	 * @return DictionaryContract
	 */
	public function transpose()
	{
		$arrayValue = $this->toList();
		$length = count(current($arrayValue));
		$result = [];
		foreach ($arrayValue as $column => $row) {
			if (count($row) != $length) {
				throw new LogicException('Child arrays do not have even length');
			}
		}

		for ($column = 0; $column < $length; $column++) {
			$result[] = array_column($arrayValue, $column);
		}

		return new Dictionary($result);
	}

	/**
	 * Returns the amount of elements in the dictionary.
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
	 * Returns the number of unique keys in this iterator. This is, the number of
	 * elements the dictionary will contain after calling `toArray()`
	 *
	 * @return int
	 */
	public function countKeys()
	{
		return count($this->toArray());
	}
}