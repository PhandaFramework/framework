<?php

namespace Phanda\Bear\Results;

use Phanda\Bear\Query\Builder;
use Phanda\Bear\Table\Table;
use Phanda\Contracts\Bear\Entity\Entity as EntityContract;
use Phanda\Contracts\Bear\Table\TableRepository;
use Phanda\Contracts\Database\Driver\Driver;
use Phanda\Contracts\Database\Statement;
use Phanda\Contracts\Bear\Query\ResultSet as ResultSetContract;
use Phanda\Dictionary\Util\DictionaryTrait;
use Phanda\Exceptions\Bear\ResultSetException;
use SplFixedArray;

/**
 * Class ResultSet
 *
 * @package Phanda\Bear\Results
 *
 * Fill this class out more to add additional functionality to the
 * dictionary that gets returned from the BearORM calls.
 */
class ResultSet implements ResultSetContract
{
	use DictionaryTrait;

	/**
	 * @var Statement
	 */
	protected $statement;

	/**
	 * Current index of iteration in fetch array
	 *
	 * @var int
	 */
	protected $index = 0;

	/**
	 * Last record fetched from the statement
	 *
	 * @var array
	 */
	protected $current;

	/**
	 * The default table
	 *
	 * @var Table|TableRepository
	 */
	protected $defaultTable;

	/**
	 * The default table alias
	 *
	 * @var string
	 */
	protected $defaultAlias;

	/**
	 * @var array
	 */
	protected $matchingMap = [];

	/**
	 * @var array
	 */
	protected $containMap = [];

	/**
	 * @var array
	 */
	protected $fieldMap = [];

	/**
	 * @var array
	 */
	protected $matchingMapColumns = [];

	/**
	 * Results that have been fetched or hydrated
	 *
	 * @var array|\ArrayAccess
	 */
	protected $results = [];

	/**
	 * Whether or not to hydrate the results into an Entity class or just return the array
	 *
	 * @var bool
	 */
	protected $hydrate = true;

	/**
	 * @var bool
	 */
	protected $useBuffering = true;

	/**
	 * @var bool
	 */
	protected $autoFields;

	/**
	 * The entity class that this result should hydrate too
	 *
	 * @var string
	 */
	protected $entityClass;

	/**
	 * @var int
	 */
	protected $count;

	/**
	 * @var Driver
	 */
	protected $driver;

	/**
	 * ResultSet constructor.
	 *
	 * @param Builder   $query
	 * @param Statement $statement
	 */
	public function __construct(Builder $query, Statement $statement)
	{
		/** @var Table $repository */
		$repository = $query->getRepository();
		$this->statement = $statement;
		$this->driver = $query->getConnection()->getDriver();
		$this->defaultTable = $repository;
		$this->hydrate = $query->isHydrationEnabled();
		$this->useBuffering = $query->isBufferedResultsEnabled();
		$this->entityClass = $repository->getEntityClass();
		$this->defaultAlias = $this->defaultTable->getAlias();
		$this->calculateColumnMap($query);
		$this->autoFields = $query->isAutoFieldsEnabled();

		if ($this->useBuffering) {
			$count = $this->count();
			$this->results = new SplFixedArray($count);
		}
	}

	/**
	 * Creates a map of row keys out of the query select clause that can be
	 * used to hydrate nested result sets more quickly.
	 *
	 * @param Builder $query
	 */
	protected function calculateColumnMap(Builder $query)
	{
		$map = [];
		foreach ($query->getClause('select') as $key => $field) {
			$key = trim($key, '"`[]');

			if (strpos($key, '__') <= 0) {
				$map[$this->defaultAlias][$key] = $key;
				continue;
			}

			$parts = explode('__', $key, 2);
			$map[$parts[0]][$key] = $parts[1];
		}

		foreach ($this->matchingMap as $alias => $assoc) {
			if (!isset($map[$alias])) {
				continue;
			}
			$this->matchingMapColumns[$alias] = $map[$alias];
			unset($map[$alias]);
		}

		$this->fieldMap = $map;
	}

	/**
	 * Returns the current record in the result iterator
	 *
	 * Part of Iterator interface.
	 *
	 * @return array|object
	 */
	public function current()
	{
		return $this->current;
	}

	/**
	 * Returns the key of the current record in the iterator
	 *
	 * Part of Iterator interface.
	 *
	 * @return int
	 */
	public function key()
	{
		return $this->index;
	}

	/**
	 * Advances the iterator pointer to the next record
	 *
	 * Part of Iterator interface.
	 *
	 * @return void
	 */
	public function next()
	{
		$this->index++;
	}

	/**
	 * Rewinds a ResultSet.
	 *
	 * Part of Iterator interface.
	 *
	 * @return void
	 */
	public function rewind()
	{
		if ($this->index == 0) {
			return;
		}

		if (!$this->useBuffering) {
			$msg = 'You cannot rewind an un-buffered ResultSet. Use Builder::enableBufferedResults() to get a buffered ResultSet.';
			throw new ResultSetException($msg);
		}

		$this->index = 0;
	}

	/**
	 * Whether there are more results to be fetched from the iterator
	 *
	 * Part of Iterator interface.
	 *
	 * @return bool
	 */
	public function valid(): bool
	{
		if ($this->useBuffering) {
			$valid = $this->index < $this->count;
			if ($valid && $this->results[$this->index] !== null) {
				$this->current = $this->results[$this->index];

				return true;
			}
			if (!$valid) {
				return $valid;
			}
		}

		$this->current = $this->fetchResult();
		$valid = $this->current !== false;

		if ($valid && $this->useBuffering) {
			$this->results[$this->index] = $this->current;
		}
		if (!$valid && $this->statement !== null) {
			$this->statement->closeCursor();
		}

		return $valid;
	}

	/**
	 * Get the first record from a result set.
	 *
	 * This method will also close the underlying statement cursor.
	 *
	 * @return array|object
	 */
	public function first()
	{
		foreach ($this as $result) {
			if ($this->statement && !$this->useBuffering) {
				$this->statement->closeCursor();
			}

			return $result;
		}

		return [];
	}

	/**
	 * Gives the number of rows in the result set.
	 *
	 * Part of the Countable interface.
	 *
	 * @return int
	 */
	public function count(): int
	{
		if ($this->count !== null) {
			return $this->count;
		}
		if ($this->statement) {
			return $this->count = $this->statement->getRowCount();
		}

		if ($this->results instanceof SplFixedArray) {
			$this->count = $this->results->count();
		} else {
			$this->count = count($this->results);
		}

		return $this->count;
	}

	/**
	 * Helper function to fetch the next result from the statement or
	 * seeded results.
	 *
	 * @return mixed
	 */
	protected function fetchResult()
	{
		if (!$this->statement) {
			return false;
		}

		$row = $this->statement->fetch(Statement::FETCH_TYPE_ASSOC);
		if ($row === false) {
			return $row;
		}

		return $this->groupResult($row);
	}

	/**
	 * Correctly nests results keys including those coming from associations
	 *
	 * Hydrates the results if enabled.
	 *
	 * @param array $row
	 * @return array|object Results
	 */
	protected function groupResult($row)
	{
		$defaultAlias = $this->defaultAlias;
		$results = $presentAliases = [];
		$options = [
			'useSetters' => false,
			'markClean' => true,
			'markNew' => false,
			'guard' => false
		];

		foreach ($this->matchingMapColumns as $alias => $keys) {
			$matching = $this->matchingMap[$alias];
			$results['_matchingData'][$alias] = array_combine(
				$keys,
				array_intersect_key($row, $keys)
			);
			if ($this->hydrate) {
				/* @var Table $table */
				$table = $matching['instance'];
				$options['source'] = $table->getRegistryAlias();
				/* @var EntityContract $entity */
				$entity = new $matching['entityClass']($results['_matchingData'][$alias], $options);
				$results['_matchingData'][$alias] = $entity;
			}
		}

		foreach ($this->fieldMap as $table => $keys) {
			$results[$table] = array_combine($keys, array_intersect_key($row, $keys));
			$presentAliases[$table] = true;
		}

		if (!isset($results[$defaultAlias])) {
			$results[$defaultAlias] = [];
		}

		unset($presentAliases[$defaultAlias]);

		foreach ($this->containMap as $assoc) {
			$alias = $assoc['nestKey'];

			if ($assoc['canBeJoined'] && empty($this->fieldMap[$alias])) {
				continue;
			}

			if (!$assoc['canBeJoined']) {
				$results[$alias] = $row[$alias];
			}

			unset($presentAliases[$alias]);

			if ($assoc['canBeJoined'] && $this->autoFields !== false) {
				$hasData = false;
				foreach ($results[$alias] as $v) {
					if ($v !== null && $v !== []) {
						$hasData = true;
						break;
					}
				}

				if (!$hasData) {
					$results[$alias] = null;
				}
			}

			if ($this->hydrate && $results[$alias] !== null && $assoc['canBeJoined']) {
				$entity = new $assoc['entityClass']($results[$alias], $options);
				$results[$alias] = $entity;
			}
		}

		foreach ($presentAliases as $alias => $present) {
			if (!isset($results[$alias])) {
				continue;
			}
			$results[$defaultAlias][$alias] = $results[$alias];
		}

		if (isset($results['_matchingData'])) {
			$results[$defaultAlias]['_matchingData'] = $results['_matchingData'];
		}

		$options['source'] = $this->defaultTable->getRegistryAlias();
		if (isset($results[$defaultAlias])) {
			$results = $results[$defaultAlias];
		}
		if ($this->hydrate && !($results instanceof EntityContract)) {
			$results = new $this->entityClass($results, $options);
		}

		return $results;
	}
}