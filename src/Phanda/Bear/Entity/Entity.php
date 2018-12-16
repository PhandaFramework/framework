<?php

namespace Phanda\Bear\Entity;

use InvalidArgumentException;
use Phanda\Contracts\Bear\Entity\Entity as EntityContract;
use Phanda\Support\PhandArr;

class Entity implements EntityContract
{
	/**
	 * @var array
	 */
	protected static $cachedAccessors = [];

	/**
	 * @var array
	 */
	protected $properties = [];

	/**
	 * @var array
	 */
	protected $original = [];

	/**
	 * @var array
	 */
	protected $hiddenProperties = [];

	/**
	 * @var array
	 */
	protected $virtualProperties = [];

	/**
	 * @var array
	 */
	protected $dirtyProperties = [];

	/**
	 * @var bool
	 */
	protected $newEntity = true;

	/**
	 * @var array
	 */
	protected $accessible = ['*' => true];

	/**
	 * @var string
	 */
	protected $registryAlias;

	/**
	 * Entity constructor.
	 *
	 * @param array $properties
	 * @param array $options
	 */
	public function __construct(array $properties = [], array $options = [])
	{
	}

	/**
	 * Sets a property in this entity
	 *
	 * @param       $property
	 * @param null  $value
	 * @param array $options
	 * @return $this
	 */
	public function set($property, $value = null, array $options = [])
	{
		if (is_string($property) && $property !== '') {
			$guard = false;
			$property = [$property => $value];
		} else {
			$guard = true;
			$options = (array)$value;
		}

		if (!is_array($property)) {
			throw new InvalidArgumentException('Cannot set an empty property');
		}
		$options += ['setter' => true, 'guard' => $guard];

		foreach ($property as $p => $value) {
			if ($options['guard'] === true && !$this->isAccessible($p)) {
				continue;
			}

			$this->setDirty($p, true);

			if (!array_key_exists($p, $this->original) &&
				array_key_exists($p, $this->properties) &&
				$this->properties[$p] !== $value
			) {
				$this->original[$p] = $this->properties[$p];
			}

			if (!$options['setter']) {
				$this->properties[$p] = $value;
				continue;
			}

			$setter = static::accessor($p, 'set');
			if ($setter) {
				$value = $this->{$setter}($value);
			}
			$this->properties[$p] = $value;
		}

		return $this;
	}

	/**
	 * Gets a property on the entity
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function get(string $property)
	{
		if (!strlen((string)$property)) {
			throw new InvalidArgumentException('Cannot get an empty property');
		}

		$value = null;
		$method = static::accessor($property, 'get');

		if (isset($this->properties[$property])) {
			$value =& $this->properties[$property];
		}

		if ($method) {
			$result = $this->{$method}($value);

			return $result;
		}

		return $value;
	}

	/**
	 * Gets the original value of a property that is on this entity
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function getOriginal(string $property)
	{
		if (!strlen($property)) {
			throw new InvalidArgumentException('Cannot get an empty property');
		}
		if (array_key_exists($property, $this->original)) {
			return $this->original[$property];
		}

		return $this->get($property);
	}

	/**
	 * Gets all original values of the entity.
	 *
	 * @return array
	 */
	public function getOriginalValues(): array
	{
		$originals = $this->original;
		$originalKeys = array_keys($originals);
		foreach ($this->properties as $key => $value) {
			if (!in_array($key, $originalKeys)) {
				$originals[$key] = $value;
			}
		}

		return $originals;
	}

	/**
	 * Checks if a property, or list of properties exists on the entity
	 *
	 * @param string|array $property
	 * @return bool
	 */
	public function has($property): bool
	{
		foreach (PhandArr::makeArray($property) as $prop) {
			if ($this->get($prop) === null) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks if a property on the entity is empty.
	 *
	 * This function behaves a little differently to the php 'empty()' function in that it will return true for an
	 * empty string, an empty array and null. Every other case will return false.
	 *
	 * @param string $property
	 * @return bool
	 */
	public function isEmpty(string $property): bool
	{
		$value = $this->get($property);
		if ($value === null
			|| (is_array($value) && empty($value)
				|| (is_string($value) && empty($value)))
		) {
			return true;
		}

		return false;
	}

	/**
	 * Returns true if a property has a not empty value
	 *
	 * @param string $property
	 * @return bool
	 */
	public function hasValue(string $property): bool
	{
		return !$this->isEmpty($property);
	}

	/**
	 * Unsets a property or a list of properties on this entity
	 *
	 * @param string|array $property
	 * @return $this
	 */
	public function unsetProperty($property)
	{
		$property = PhandArr::makeArray($property);
		foreach ($property as $p) {
			unset($this->properties[$p], $this->dirtyProperties[$p]);
		}

		return $this;
	}



}