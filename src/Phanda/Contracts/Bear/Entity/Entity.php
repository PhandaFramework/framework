<?php

namespace Phanda\Contracts\Bear\Entity;

interface Entity extends \ArrayAccess, \JsonSerializable
{
	/**
	 * Gets all original values of the entity.
	 *
	 * @return array
	 */
	public function getOriginalValues(): array;

	/**
	 * Returns true if a property has a not empty value
	 *
	 * @param string $property
	 * @return bool
	 */
	public function hasValue(string $property): bool;

	/**
	 * Checks if a property on the entity is empty.
	 *
	 * This function behaves a little differently to the php 'empty()' function in that it will return true for an
	 * empty string, an empty array and null. Every other case will return false.
	 *
	 * @param string $property
	 * @return bool
	 */
	public function isEmpty(string $property): bool;

	/**
	 * Gets a property on the entity
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function get(string $property);

	/**
	 * Sets a list of properties to be hidden.
	 *
	 * Hidden properties will not appear in array exports
	 *
	 * @param array $properties
	 * @param bool  $merge
	 * @return $this
	 */
	public function setHidden(array $properties, bool $merge = false);

	/**
	 * Gets the hidden properties.
	 *
	 * @return array
	 */
	public function getHidden(): array;

	/**
	 * Sets a list of virtual properties on this entity
	 *
	 * @param array $properties
	 * @param bool  $merge
	 * @return $this
	 */
	public function setVirtual(array $properties, bool $merge = false);

	/**
	 * Gets the virtual properties on this entity
	 *
	 * @return array
	 */
	public function getVirtualProperties(): array;

	/**
	 * Converts the entities visible properties to an array
	 *
	 * @return array
	 */
	public function toArray(): array;

	/**
	 * Gets all the visible properties on this entity that are not in the hidden property list.
	 *
	 * @return array
	 */
	public function getVisibleProperties();

	/**
	 * Returns the properties that will be serialized as JSON
	 *
	 * @return array
	 */
	public function jsonSerialize(): array;

	/**
	 * Extracts the properties from the entity
	 *
	 * @param array $properties
	 * @param bool  $onlyDirty
	 * @return array
	 */
	public function extract(array $properties, $onlyDirty = false): array;

	/**
	 * Checks if the entity is dirty or if a single property of it is dirty.
	 *
	 * @param string|null $property
	 * @return bool
	 */
	public function isDirty(?string $property = null): bool;

	/**
	 * Implements isset($entity);
	 *
	 * @param mixed $offset The offset to check.
	 * @return bool Success
	 */
	public function offsetExists($offset): bool;

	/**
	 * Checks if a property, or list of properties exists on the entity
	 *
	 * @param string|array $property
	 * @return bool
	 */
	public function has($property): bool;

	/**
	 * Implements $entity[$offset];
	 *
	 * @param mixed $offset The offset to get.
	 * @return mixed
	 */
	public function offsetGet($offset);

	/**
	 * Implements $entity[$offset] = $value;
	 *
	 * @param mixed $offset The offset to set.
	 * @param mixed $value  The value to set.
	 * @return void
	 */
	public function offsetSet($offset, $value);

	/**
	 * Sets a property in this entity
	 *
	 * @param       $property
	 * @param null  $value
	 * @param array $options
	 * @return $this
	 */
	public function set($property, $value = null, array $options = []);

	/**
	 * Checks if a property is accessible
	 *
	 * @param string $property
	 * @return bool
	 */
	public function isAccessible(string $property): bool;

	/**
	 * Sets the dirty status of a single property.
	 *
	 * @param string $property
	 * @param bool   $isDirty
	 * @return $this
	 */
	public function setDirty(string $property, $isDirty = true);

	/**
	 * Implements unset($result[$offset]);
	 *
	 * @param mixed $offset The offset to remove.
	 * @return void
	 */
	public function offsetUnset($offset);

	/**
	 * Unsets a property or a list of properties on this entity
	 *
	 * @param string|array $property
	 * @return $this
	 */
	public function unsetProperty($property);

	/**
	 * Extracts a list of only the original properties
	 *
	 * @param array $properties
	 * @return array
	 */
	public function extractOriginal(array $properties): array;

	/**
	 * Gets the original value of a property that is on this entity
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function getOriginalProperties(string $property);

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
	public function extractOriginalChanged(array $properties): array;

	/**
	 * Gets the dirty properties.
	 *
	 * @return string[]
	 */
	public function getDirty(): array;

	/**
	 * Sets the entire entity as clean, which means that it will appear as
	 * no properties being modified or added at all. This is an useful call
	 * for an initial object hydration
	 *
	 * @return void
	 */
	public function clean();

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
	public function isNew(?bool $new = null): bool;

	/**
	 * Stores whether or not a property value can be changed or set in this entity.
	 *
	 * @param string|array $property
	 * @param bool         $set
	 * @return $this
	 */
	public function setAccess($property, bool $set);

	/**
	 * Returns the alias of the repository from which this entity came from.
	 *
	 * @return string
	 */
	public function getSource();

	/**
	 * Sets the source alias
	 *
	 * @param string $alias
	 * @return $this
	 */
	public function setSource($alias);
}