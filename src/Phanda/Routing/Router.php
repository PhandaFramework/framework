<?php

namespace Phanda\Routing;

use Phanda\Contracts\Events\Dispatcher;
use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Routing\Route;
use Phanda\Contracts\Routing\Router as RouterContract;

class Router implements RouterContract
{
    /**
     * @var Dispatcher
     */
    protected $eventDispatcher;

    /**
     * @var Application
     */
    protected $phanda;

    public function __construct(Dispatcher $eventDispatcher, Application $phanda)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->phanda = $phanda;
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function get($uri, $action)
    {
        // TODO: Implement get() method.
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function post($uri, $action)
    {
        // TODO: Implement post() method.
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function put($uri, $action)
    {
        // TODO: Implement put() method.
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function delete($uri, $action)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function patch($uri, $action)
    {
        // TODO: Implement patch() method.
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function options($uri, $action)
    {
        // TODO: Implement options() method.
    }

    /**
     * @param array|string $methods
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function match($methods, $uri, $action)
    {
        // TODO: Implement match() method.
    }

    /**
     * @param array $attributes
     * @param \Closure|string $routes
     * @return void
     */
    public function group(array $attributes, $routes)
    {
        // TODO: Implement group() method.
    }
}