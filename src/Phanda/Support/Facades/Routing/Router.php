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
 * @see \Phanda\Contracts\Routing\Router
 * @see RouteRegistrar
 *
 * @method static Route get(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method static Route post(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method static Route put(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method static Route delete(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method static Route patch(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method static Route options(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method static Route any(string $name, string $uri, \Closure|array|string|null $action = null)
 *
 * @method static RouteRegistrar domain(string $value)
 * @method static RouteRegistrar middleware(array|string|null $middleware)
 * @method static RouteRegistrar name(string $value)
 * @method static RouteRegistrar namespace(string $value)
 * @method static RouteRegistrar prefix(string  $prefix)
 * @method static RouteRegistrar where(array  $where)
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
        static::addImplementation('routerFacade', \Phanda\Contracts\Routing\Router::class);
    }
}