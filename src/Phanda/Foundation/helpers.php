<?php

use Phanda\Container\Container;
use Phanda\Foundation\Application;

if (!function_exists('app')) {
    /**
     * @param  string $abstract
     * @param  array $parameters
     * @return mixed|Application
     */
    function app($abstract = null, array $parameters = [])
    {
        return phanda($abstract, $parameters);
    }
}

if (!function_exists('phanda')) {
    /**
     * Get the available container instance.
     *
     * @param  string $abstract
     * @param  array $parameters
     * @return mixed|Application
     */
    function phanda($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->create($abstract, $parameters);
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string $path
     * @return string
     */
    function app_path($path = '')
    {
        return phanda()->appPath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string $path
     * @return string
     */
    function base_path($path = '')
    {
        return phanda()->basePath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public serving path.
     *
     * @param  string $path
     * @return string
     */
    function public_path($path = '')
    {
        return phanda()->publicPath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
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