<?php

namespace Phanda\Contracts\Caching;

use Phanda\Contracts\Support\Repository;

interface CacheRepository
{
    /**
     * Checks if a value exists in the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key);

    /**
     * Gets a value by the key
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Returns all the items in the cache.
     *
     * @return array
     */
    public function all();

    /**
     * Sets a value in the cache, and returns the value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     * @return mixed
     */
    public function set($key, $value = null);

    /**
     * Checks if a given value is newer than the currently cached.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function outdated($key, $value);

    /**
     * Updates a value in the cache if it is outdated and returns the value
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function updateIfOutdated($key, $value);
}