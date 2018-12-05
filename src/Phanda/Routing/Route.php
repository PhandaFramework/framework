<?php

namespace Phanda\Routing;

use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Routing\Route as RouteContract;
use Phanda\Http\AbstractController;
use Phanda\Support\Routing\RouteActionParser;
use Symfony\Component\Routing\CompiledRoute;

class Route implements RouteContract
{
    /**
     * @var string
     */
    public $uri;

    /**
     * @var array
     */
    public $methods;

    /**
     * @var array
     */
    public $action;

    /**
     * @var AbstractController|null
     */
    public $controller;

    /**
     * @var array
     */
    public $defaults = [];

    /**
     * @var array
     */
    public $parameters;

    /**
     * @var array|null
     */
    public $parameterNames;

    /**
     * @var CompiledRoute
     */
    public $compiledRoute;

    /**
     * @var \Phanda\Contracts\Routing\Router
     */
    protected $router;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Route constructor.
     *
     * @param string $uri
     * @param array|string $methods
     * @param \Closure|array $action
     */
    public function __construct($uri, $methods, $action)
    {
        $this->uri = $uri;
        $this->methods = (array)$methods;
        $this->action = $this->parseAction($action);
    }

    /**
     * @param  callable|array|null  $action
     * @return array
     */
    protected function parseAction($action)
    {
        return RouteActionParser::parse($this->uri, $action);
    }

    /**
     * Perform route action, return result.
     *
     * @return mixed
     */
    public function run()
    {

    }
}