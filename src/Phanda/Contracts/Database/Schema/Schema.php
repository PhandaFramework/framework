<?php

namespace Phanda\Contracts\Database\Schema;

interface Schema
{
    /**
     * Get the name of the table.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Add a column to the table.
     *
     * @param string $name
     * @param array|string $attrs
     * @return $this
     */
    public function addColumn(string $name, $attrs);

    /**
     * Get column data in the table.
     *
     * @param string $name
     * @return array|null
     */
    public function getColumn(string $name): ?array;

    /**
     * Returns true if a column exists in the schema.
     *
     * @param string $name Column name.
     * @return bool
     */
    public function hasColumn(string $name): bool;

    /**
     * Remove a column from the table schema.
     *
     * @param string $name
     * @return $this
     */
    public function removeColumn(string $name);

    /**
     * Get the column names in the table.
     *
     * @return string[]
     */
    public function columns(): array;

    /**
     * Returns column type or null if a column does not exist.
     *
     * @param string $name
     * @return string|null
     */
    public function getColumnType(string $name): ?string;

    /**
     * Sets the type of a column.
     *
     * @param string $name
     * @param string $type
     * @return $this
     */
    public function setColumnType(string $name, string $type);

    /**
     * Check whether or not a field is nullable
     *
     * @param string $name
     * @return bool
     */
    public function isNullable(string $name): bool;

    /**
     * Returns an array where the keys are the column names in the schema
     * and the values the database type they have.
     *
     * @return array
     */
    public function typeMap(): array;

    /**
     * Get a hash of columns and their default values.
     *
     * @return array
     */
    public function defaultValues(): array;

    /**
     * Sets the options for a table.
     *
     * Table options allow you to set platform specific table level options.
     * For example the engine type in MySQL.
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options);

    /**
     * Gets the options for a table.
     *
     * Table options allow you to set platform specific table level options.
     * For example the engine type in MySQL.
     *
     * @return array An array of options.
     */
    public function getOptions(): array;
}