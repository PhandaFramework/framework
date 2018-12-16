<?php

namespace Phanda\Bear\Results;

use Phanda\Bear\Query\Builder;
use Phanda\Bear\Table\Table;
use Phanda\Contracts\Bear\Table\TableRepository;
use Phanda\Contracts\Database\Driver\Driver;
use Phanda\Contracts\Database\Statement;
use Phanda\Contracts\Bear\Query\ResultSet as ResultSetContract;
use Phanda\Dictionary\Util\DictionaryTrait;

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
		$this->entityClass = $repository->getEntityClass();
		$this->defaultAlias = $this->defaultTable->getAlias();
		$this->calculateColumnMap($query);
		$this->autoFields = $query->isAutoFieldsEnabled();
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
}