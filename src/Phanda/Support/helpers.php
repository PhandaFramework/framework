<?php

use Phanda\Contracts\Support\Htmlable;
use Phanda\Support\PhandArr;

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed $target
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if (!is_array($target)) {
                    return value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }

                return in_array('*', $key) ? PhandArr::flatten($result) : $result;
            }

            if (PhandArr::accessible($target) && PhandArr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if (! function_exists('environment')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function environment($key = null, $default = null)
    {
        /** @var \Phanda\Environment\Repository $environment */
        $environment = phanda('environment');

        if(is_null($key)) {
            return $environment;
        }

        if (is_array($key)) {
            $environment->set($key);
            return null;
        }

        $value = $environment->get($key, $default);

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }
}



if (! function_exists('config')) {
    /**
     * @param null|string|array $key
     * @param mixed $default
     * @return \Phanda\Configuration\Repository|null
     */
    function config($key = null, $default = null)
    {
        /** @var \Phanda\Configuration\Repository $config */
        $config = phanda('config');

        if (is_null($key)) {
            return $config;
        }

        if (is_array($key)) {
            $config->set($key);
            return null;
        }

        return $config->get($key, $default);
    }
}

if (! function_exists('modify')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param  mixed  $value
     * @param  callable|null  $callback
     * @return mixed
     */
    function modify($value, $callback)
    {
        $callback($value);
        return $value;
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('e')) {
    /**
     * Escape HTML special characters in a string.
     *
     * @param  Htmlable|string  $value
     * @param  bool  $doubleEncode
     * @return string
     */
    function e($value, $doubleEncode = true)
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}



if(!function_exists('createDictionary')) {
    /**
     * Creates a new Dictionary with the given items.
     *
     * @param array $items
     * @return \Phanda\Support\Dictionary
     */
    function createDictionary($items = [])
    {
        return new \Phanda\Support\Dictionary($items);
    }
}
