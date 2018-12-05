<?php

namespace Phanda\Routing;

use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Routing\Route as RouteContract;
use Phanda\Exceptions\Foundation\Http\HttpResponseException;
use Phanda\Routing\Controller\AbstractController;
use Phanda\Support\Routing\RouteActionParser;
use Phanda\Util\Routing\ResolveRouteDependenciesTrait;
use Symfony\Component\Routing\CompiledRoute;

class Route implements RouteContract
{
    use ResolveRouteDependenciesTrait;

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

        if (in_array('GET', $this->methods) && !in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
        }
    }

    /**
     * @param  callable|array|null $action
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
        $this->container = $this->container ?: new \Phanda\Container\Container();

        try {
            if ($this->isActionOnController()) {
                return $this->runControllerMethod();
            }

            return $this->runCallableMethod();
        } catch (HttpResponseException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return bool
     */
    protected function isActionOnController()
    {
        return is_string($this->action['method']);
    }

    protected function runControllerMethod()
    {

    }

    protected function runCallableMethod()
    {

    }
}