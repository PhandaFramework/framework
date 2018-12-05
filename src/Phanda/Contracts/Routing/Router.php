<?php


namespace Phanda\Contracts\Routing;


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
     * @param array|string $methods
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function match($name, $methods, $uri, $action);

    /**
     * @param string $name
     * @param array $attributes
     * @param \Closure|string $routes
     * @return void
     */
    public function group($name, array $attributes, $routes);

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

}