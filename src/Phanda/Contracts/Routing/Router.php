<?php


namespace Phanda\Contracts\Routing;


interface Router
{

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function get($uri, $action);

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function post($uri, $action);

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function put($uri, $action);

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function delete($uri, $action);

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function patch($uri, $action);

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function options($uri, $action);

    /**
     * @param array|string $methods
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function match($methods, $uri, $action);

    /**
     * @param array $attributes
     * @param \Closure|string $routes
     * @return void
     */
    public function group(array $attributes, $routes);

}