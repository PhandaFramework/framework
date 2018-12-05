<?php


namespace Phanda\Contracts\Routing;

use Phanda\Foundation\Http\Request;
use Phanda\Routing\RouteRegistrar;
use Phanda\Routing\RouteRepository;

/**
 * @mixin RouteRegistrar
 */
interface Router
{

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function get($name, $uri, $action);

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function post($name, $uri, $action);

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function put($name, $uri, $action);

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function delete($name, $uri, $action);

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function patch($name, $uri, $action);

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function options($name, $uri, $action);

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function any($name, $uri, $action);

    /**
     * @param string $name
     * @param array|string $methods
     * @param string $uri
     * @param \Closure|array|string|callable|null $action
     */
    public function addRoute($name, $methods, $uri, $action);

    /**
     * @param array $attributes
     * @param \Closure|string $routes
     */
    public function groupRoutes(array $attributes, $routes);

    /**
     * Determine if the router currently has a group stack.
     *
     * @return bool
     */
    public function hasGroupStack();

    /**
     * @param Request $request
     * @return mixed
     */
    public function dispatch(Request $request);

    /**
     * @param Request $request
     * @return mixed
     */
    public function dispatchToRoute(Request $request);

    /**
     * Get the underlying route repository.
     *
     * @return RouteRepository
     */
    public function getRoutes();

    /**
     * @return array
     */
    public function getGroupStack();

}