<?php

namespace Phanda\Support;

use ArrayAccess;

class PhandArr
{
    /**
     * Makes a value an array if not an array
     *
     * @param $value
     * @return array
     */
    public static function makeArray($value) {
        if(is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Filters an array
     *
     * @param $array
     * @param callable $callback
     * @return array
     */
    public static function filter($array, callable $callback)
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Flattens an array of arrays
     *
     * @param $array
     * @return array
     */
    public static function flatten($array)
    {
        $results = [];

        foreach ($array as $values) {
            if (! is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return $results;
    }

    /**
     * Checks if a value is an array, or can be accessed like an array
     *
     * @param $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determines if an offset exists on an array, or accessible class
     *
     * @param $array
     * @param $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

}