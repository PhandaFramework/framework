<?php

namespace Phanda\Bear\Entity;

use InvalidArgumentException;
use Phanda\Contracts\Bear\Entity\Entity as EntityContract;
use Phanda\Support\PhandaInflector;
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
	protected $originalProperties = [];

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
		$options += [
			'useSetters' => true,
			'markClean' => false,
			'markNew' => null,
			'guard' => false,
			'source' => null
		];

		if (!empty($options['source'])) {
			$this->setSource($options['source']);
		}

		if ($options['markNew'] !== null) {
			$this->isNew($options['markNew']);
		}

		if (!empty($properties) && $options['markClean'] && !$options['useSetters']) {
			$this->properties = $properties;

			return;
		}

		if (!empty($properties)) {
			$this->set($properties, [
				'setter' => $options['useSetters'],
				'guard' => $options['guard']
			]);
		}

		if ($options['markClean']) {
			$this->clean();
		}
	}

	/**
	 * Gets all original values of the entity.
	 *
	 * @return array
	 */
	public function getOriginalValues(): array
	{
		$originals = $this->originalProperties;
		$originalKeys = array_keys($originals);
		foreach ($this->properties as $key => $value) {
			if (!in_array($key, $originalKeys)) {
				$originals[$key] = $value;
			}
		}

		return $originals;
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
	 * Gets a custom accessor defined on the entity.
	 *
	 * For registering custom accessors and mutators, please see the Phanda documentation
	 *
	 * @param $property
	 * @param $type
	 * @return string
	 */
	protected static function accessor($property, $type)
	{
		$class = static::class;

		if (isset(static::$cachedAccessors[$class][$type][$property])) {
			return static::$cachedAccessors[$class][$type][$property];
		}

		if (!empty(static::$cachedAccessors[$class])) {
			return static::$cachedAccessors[$class][$type][$property] = '';
		}

		if ($class === Entity::class) {
			return '';
		}

		foreach (get_class_methods($class) as $method) {
			$prefix = substr($method, 1, 3);
			if ($method[0] !== '_' || ($prefix !== 'get' && $prefix !== 'set')) {
				continue;
			}
			$field = lcfirst(substr($method, 4));
			$snakeField = PhandaInflector::underscore($field);
			$titleField = ucfirst($field);
			static::$cachedAccessors[$class][$prefix][$snakeField] = $method;
			static::$cachedAccessors[$class][$prefix][$field] = $method;
			static::$cachedAccessors[$class][$prefix][$titleField] = $method;
		}

		if (!isset(static::$cachedAccessors[$class][$type][$property])) {
			static::$cachedAccessors[$class][$type][$property] = '';
		}

		return static::$cachedAccessors[$class][$type][$property];
	}

	/**
	 * Sets a list of properties to be hidden.
	 *
	 * Hidden properties will not appear in array exports
	 *
	 * @param array $properties
	 * @param bool  $merge
	 * @return $this
	 */
	public function setHidden(array $properties, bool $merge = false)
	{
		if ($merge === false) {
			$this->hiddenProperties = $properties;

			return $this;
		}

		$properties = array_merge($this->hiddenProperties, $properties);
		$this->hiddenProperties = array_unique($properties);

		return $this;
	}

	/**
	 * Gets the hidden properties.
	 *
	 * @return array
	 */
	public function getHidden(): array
	{
		return $this->hiddenProperties;
	}

	/**
	 * Sets a list of virtual properties on this entity
	 *
	 * @param array $properties
	 * @param bool  $merge
	 * @return $this
	 */
	public function setVirtual(array $properties, bool $merge = false)
	{
		if ($merge === false) {
			$this->virtualProperties = $properties;

			return $this;
		}

		$properties = array_merge($this->virtualProperties, $properties);
		$this->virtualProperties = array_unique($properties);

		return $this;
	}

	/**
	 * Gets the virtual properties on this entity
	 *
	 * @return array
	 */
	public function getVirtualProperties(): array
	{
		return $this->virtualProperties;
	}

	/**
	 * Converts the entities visible properties to an array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$result = [];
		foreach ($this->getVisibleProperties() as $property) {
			$value = $this->get($property);
			if (is_array($value)) {
				$result[$property] = [];
				foreach ($value as $k => $entity) {
					if ($entity instanceof EntityContract) {
						$result[$property][$k] = $entity->toArray();
					} else {
						$result[$property][$k] = $entity;
					}
				}
			} elseif ($value instanceof EntityContract) {
				$result[$property] = $value->toArray();
			} else {
				$result[$property] = $value;
			}
		}

		return $result;
	}

	/**
	 * Gets all the visible properties on this entity that are not in the hidden property list.
	 *
	 * @return array
	 */
	public function getVisibleProperties()
	{
		$properties = array_keys($this->properties);
		$properties = array_merge($properties, $this->virtualProperties);

		return array_diff($properties, $this->hiddenProperties);
	}

	/**
	 * Returns the properties that will be serialized as JSON
	 *
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return $this->extract($this->getVisibleProperties());
	}

	/**
	 * Extracts the properties from the entity
	 *
	 * @param array $properties
	 * @param bool  $onlyDirty
	 * @return array
	 */
	public function extract(array $properties, $onlyDirty = false): array
	{
		$result = [];
		foreach ($properties as $property) {
			if (!$onlyDirty || $this->isDirty($property)) {
				$result[$property] = $this->get($property);
			}
		}

		return $result;
	}

	/**
	 * Checks if the entity is dirty or if a single property of it is dirty.
	 *
	 * @param string|null $property
	 * @return bool
	 */
	public function isDirty(?string $property = null): bool
	{
		if ($property === null) {
			return !empty($this->dirtyProperties);
		}

		return isset($this->dirtyProperties[$property]);
	}

	/**
	 * Implements isset($entity);
	 *
	 * @param mixed $offset The offset to check.
	 * @return bool Success
	 */
	public function offsetExists($offset): bool
	{
		return $this->has($offset);
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
	 * Implements $entity[$offset];
	 *
	 * @param mixed $offset The offset to get.
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Implements $entity[$offset] = $value;
	 *
	 * @param mixed $offset The offset to set.
	 * @param mixed $value  The value to set.
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
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

			if (!array_key_exists($p, $this->originalProperties) &&
				array_key_exists($p, $this->properties) &&
				$this->properties[$p] !== $value
			) {
				$this->originalProperties[$p] = $this->properties[$p];
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
	 * Checks if a property is accessible
	 *
	 * @param string $property
	 * @return bool
	 */
	public function isAccessible(string $property): bool
	{
		$value = isset($this->accessible[$property]) ?
			$this->accessible[$property] :
			null;

		return ($value === null && !empty($this->accessible['*'])) || $value;
	}

	/**
	 * Sets the dirty status of a single property.
	 *
	 * @param string $property
	 * @param bool   $isDirty
	 * @return $this
	 */
	public function setDirty(string $property, $isDirty = true)
	{
		if ($isDirty === false) {
			unset($this->dirtyProperties[$property]);

			return $this;
		}

		$this->dirtyProperties[$property] = true;
		return $this;
	}

	/**
	 * Implements unset($result[$offset]);
	 *
	 * @param mixed $offset The offset to remove.
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		$this->unsetProperty($offset);
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

	/**
	 * Extracts a list of only the original properties
	 *
	 * @param array $properties
	 * @return array
	 */
	public function extractOriginal(array $properties): array
	{
		$result = [];
		foreach ($properties as $property) {
			$result[$property] = $this->getOriginalProperties($property);
		}

		return $result;
	}

	/**
	 * Gets the original value of a property that is on this entity
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function getOriginalProperties(string $property)
	{
		if (!strlen($property)) {
			throw new InvalidArgumentException('Cannot get an empty property');
		}
		if (array_key_exists($property, $this->originalProperties)) {
			return $this->originalProperties[$property];
		}

		return $this->get($property);
	}

	/**
	 * Returns an array with only the original properties
	 * stored in this entity, indexed by property name.
	 *
	 * This method will only return properties that have been modified since
	 * the entity was built. Unchanged properties will be omitted.
	 *
	 * @param array $properties
	 * @return array
	 */
	public function extractOriginalChanged(array $properties): array
	{
		$result = [];
		foreach ($properties as $property) {
			$original = $this->getOriginalProperties($property);
			if ($original !== $this->get($property)) {
				$result[$property] = $original;
			}
		}

		return $result;
	}

	/**
	 * Gets the dirty properties.
	 *
	 * @return string[]
	 */
	public function getDirty(): array
	{
		return array_keys($this->dirtyProperties);
	}

	/**
	 * Sets the entire entity as clean, which means that it will appear as
	 * no properties being modified or added at all. This is an useful call
	 * for an initial object hydration
	 *
	 * @return void
	 */
	public function clean()
	{
		$this->dirtyProperties = [];
		$this->originalProperties = [];
	}

	/**
	 * Returns whether or not this entity has already been persisted.
	 * This method can return null in the case there is no prior information on
	 * the status of this entity.
	 *
	 * If called with a boolean it will set the known status of this instance,
	 * true means that the instance is not yet persisted in the database, false
	 * that it already is.
	 *
	 * @param bool|null $new
	 * @return bool
	 */
	public function isNew(?bool $new = null): bool
	{
		if ($new === null) {
			return $this->newEntity;
		}

		$new = (bool)$new;

		if ($new) {
			foreach ($this->properties as $k => $p) {
				$this->dirtyProperties[$k] = true;
			}
		}

		return $this->newEntity = $new;
	}

	/**
	 * Stores whether or not a property value can be changed or set in this entity.
	 *
	 * @param string|array $property
	 * @param bool         $set
	 * @return $this
	 */
	public function setAccess($property, bool $set)
	{
		if ($property === '*') {
			$this->accessible = array_map(function ($p) use ($set) {
				return $set;
			}, $this->accessible);
			$this->accessible['*'] = $set;

			return $this;
		}

		foreach (PhandArr::makeArray($property) as $prop) {
			$this->accessible[$prop] = $set;
		}

		return $this;
	}

	/**
	 * Returns the alias of the repository from which this entity came from.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return $this->registryAlias;
	}

	/**
	 * Sets the source alias
	 *
	 * @param string $alias
	 * @return $this
	 */
	public function setSource($alias)
	{
		$this->registryAlias = $alias;
		return $this;
	}

	/**
	 * Returns a string representation of this object in a human readable format.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return json_encode($this, JSON_PRETTY_PRINT);
	}

	/**
	 * Magic getter to access properties that have been set in this entity
	 *
	 * @param string $property Name of the property to access
	 * @return mixed
	 */
	public function __get(string $property)
	{
		return $this->get($property);
	}

	/**
	 * Magic setter to add or edit a property in this entity
	 *
	 * @param string $property The name of the property to set
	 * @param mixed  $value    The value to set to the property
	 * @return void
	 */
	public function __set(string $property, $value)
	{
		$this->set($property, $value);
	}

	/**
	 * Returns whether this entity contains a property named $property
	 * regardless of if it is empty.
	 *
	 * @param string $property
	 * @return bool
	 */
	public function __isset(string $property): bool
	{
		return $this->has($property);
	}

	/**
	 * Removes a property from this entity
	 *
	 * @param string $property
	 * @return void
	 */
	public function __unset(string $property)
	{
		$this->unsetProperty($property);
	}
}