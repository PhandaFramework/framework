<?php

namespace Phanda\Dictionary;

use ArrayIterator;
use Phanda\Contracts\Dictionary\Dictionary as DictionaryContract;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Contracts\Support\Jsonable;
use Phanda\Dictionary\Iterator\ExtractIterator;
use Phanda\Dictionary\Iterator\FilterIterator;
use Phanda\Dictionary\Iterator\MapReduceIterator;
use Phanda\Dictionary\Iterator\UnfoldIterator;
use RecursiveIteratorIterator;
use Traversable;

class Dictionary extends \IteratorIterator implements Arrayable, \ArrayAccess, \Countable, DictionaryContract
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * Dictionary constructor.
     *
     * @param mixed $items
     */
    public function __construct($items = [])
    {
        $this->items = $this->convertItemsToArray($items);

        parent::__construct(new ArrayIterator($this->items));
    }

    /**
     * @param $items
     * @return array
     */
    protected function convertItemsToArray($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->toArray();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof \JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof \Traversable) {
            return iterator_to_array($items);
        }

        return (array)$items;
    }

    /**
     * Converts the Dictionary to an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * Gets all the items in the dictionary
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Run a map over each of the items in the dictionary.
     *
     * @param callable $callback
     * @return DictionaryContract
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);
        return new static(array_combine($keys, $items));
    }

    /**
     * Pushes an item to the end of the array
     *
     * @param $value
     * @return DictionaryContract
     */
    public function push($value)
    {
        $this->offsetSet(null, $value);
        return $this;
    }

    /**
     * Sets an offset
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Checks if an offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Gets an offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * Counts the elements in the dictionary.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Returns the first result in this dictionary
     *
     * @return mixed The first value in the dictionary will be returned.
     */
    public function first()
    {
        return $this->items[0] ?? [];
    }

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
     * @param array $path Path to extract from.
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
     * @inheritdoc
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
     * Returns a column from $data that can be extracted
     * by iterating over the column names contained in $path
     *
     * @param array|\ArrayAccess $data Data.
     * @param array $path Path to extract from.
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
     * Creates a new dictionary where the items are the
     * concatenation of the lists of items generated by the transformer function
     * applied to each item in the original dictionary.
     *
     * @param callable|null $transformer
     * @return DictionaryContract
     */
    public function unfold(?callable $transformer = null)
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
     * {@inheritDoc}
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
                return $isObject ? $value : $value->getArrayCopy();
            });
    }
}