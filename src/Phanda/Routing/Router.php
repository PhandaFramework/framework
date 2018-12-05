<?php

namespace Phanda\Routing;

use Phanda\Contracts\Events\Dispatcher;
use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Routing\Route;
use Phanda\Contracts\Routing\Router as RouterContract;
use Phanda\Foundation\Http\Request;

class Router implements RouterContract
{
    public const VERBS = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * @var Dispatcher
     */
    protected $eventDispatcher;

    /**
     * @var Application
     */
    protected $phanda;

    /**
     * The route collection instance.
     *
     * @var RouteRepository
     */
    protected $routes;

    /**
     * @var Route
     */
    protected $currentRoute;

    /**
     * @var Request
     */
    protected $currentRequest;

    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * Router constructor.
     * @param Dispatcher $eventDispatcher
     * @param Application $phanda
     */
    public function __construct(Dispatcher $eventDispatcher, Application $phanda)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->phanda = $phanda;
        $this->routes = new RouteRepository();
    }

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function get($name, $uri, $action)
    {
        // TODO: Implement get() method.
    }

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function post($name, $uri, $action)
    {
        // TODO: Implement post() method.
    }

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function put($name, $uri, $action)
    {
        // TODO: Implement put() method.
    }

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function delete($name, $uri, $action)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function patch($name, $uri, $action)
    {
        // TODO: Implement patch() method.
    }

    /**
     * @param string $name
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function options($name, $uri, $action)
    {
        // TODO: Implement options() method.
    }

    /**
     * @param string $name
     * @param array|string $methods
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @return Route
     */
    public function match($name, $methods, $uri, $action)
    {
        // TODO: Implement match() method.
    }

    /**
     * @param string $name
     * @param array $attributes
     * @param \Closure|string $routes
     * @return void
     */
    public function group($name, array $attributes, $routes)
    {
        // TODO: Implement group() method.
    }

    /**
     * @param string $name
     * @param array|string $methods
     * @param string $uri
     * @param \Closure|array|string|callable|null $action
     */
    public function addRoute($name, $methods, $uri, $action)
    {

    }

    protected function createRoute()
    {

    }

    /**
     * @param array $attributes
     * @param \Closure|string $routes
     */
    public function groupRoutes(array $attributes, $routes)
    {

    }
}