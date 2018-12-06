<?php

namespace Phanda\Support\Facades\Routing;

use Phanda\Contracts\Routing\Route;
use Phanda\Routing\RouteRegistrar;
use Phanda\Support\Facades\Facade;

/**
 * Class Router
 * The Base Phanda Router Facade
 *
 * @package Phanda\Support\Facades\Routing
 *
 * @method Route get(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route post(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route put(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route delete(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route patch(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route options(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route any(string $name, string $uri, \Closure|array|string|null $action = null)
 *
 * @method RouteRegistrar domain(string $value)
 * @method RouteRegistrar middleware(array|string|null $middleware)
 * @method RouteRegistrar name(string $value)
 * @method RouteRegistrar namespace(string $value)
 * @method RouteRegistrar prefix(string  $prefix)
 * @method RouteRegistrar where(array  $where)
 */
class Router extends Facade
{

    /**
     * Set up the name of the Facade instance being resolved. Used internally for checking if the Facade has been
     * resolved or not.
     *
     * @return string
     */
    protected static function getFacadeName(): string
    {
        return "router";
    }

    /**
     * Sets up the facade implementations by calling static::addImplementation($name, $implementation) for each of the
     * implementations this Facade has.
     *
     * @return void
     */
    protected static function setupFacadeImplementations()
    {
        static::addImplementation('router', \Phanda\Contracts\Routing\Router::class);
    }
}