<?php

namespace Phanda\Routing;

use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Routing\Route as RouteContract;
use Phanda\Exceptions\Foundation\Http\HttpResponseException;
use Phanda\Foundation\Http\Request;
use Phanda\Routing\Controller\AbstractController;
use Phanda\Support\PhandaStr;
use Phanda\Support\Routing\RouteActionParser;
use Phanda\Support\Routing\RouteCompiler;
use Phanda\Util\Routing\ResolveRouteDependenciesTrait;
use ReflectionFunction;
use Symfony\Component\Routing\CompiledRoute;
use Phanda\Contracts\Routing\Controller\Dispatcher as ControllerDispatcherContract;
use Phanda\Routing\Controller\Dispatcher as ControllerDispatcher;

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
    public $conditionals = [];

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
     *
     * @throws \ReflectionException
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

    /**
     * @return mixed
     */
    protected function runControllerMethod()
    {
        return $this->getControllerDispatcher()->dispatch(
            $this,
            $this->getRouteController(),
            $this->getRouteControllerMethod()
        );
    }

    /**
     * @return AbstractController
     */
    public function getRouteController()
    {
        if(!$this->controller) {
            $class = $this->parseRouteControllerMethod()[0];
            $this->controller = $this->container->create(ltrim($class, '\\'));
        }

        return $this->controller;
    }

    /**
     * @return string
     */
    protected function getRouteControllerMethod()
    {
        return $this->parseRouteControllerMethod()[1];
    }

    /**
     * @return array
     */
    protected function parseRouteControllerMethod()
    {
        return PhandaStr::parseClassAtMethod($this->action['method']);
    }

    /**
     * @return mixed
     *
     * @throws \ReflectionException
     */
    protected function runCallableMethod()
    {
        $method = $this->action['method'];

        return $method(...array_values($this->resolveMethodDependencies(
            $this->getParametersWithoutNulls(), new ReflectionFunction($method)
        )));
    }

    /**
     * @return CompiledRoute
     */
    protected function compileRoute()
    {
        if (! $this->compiledRoute) {
            $this->compiledRoute = (new RouteCompiler($this))->compile();
        }

        return $this->compiledRoute;
    }

    /**
     * Get the key / value list of parameters without null values.
     *
     * @return array
     */
    public function getParametersWithoutNulls()
    {
        // TODO: Implement parametersWithoutNulls() method.
    }

    /**
     * Get the key / value list of parameters for the route.
     *
     * @return array
     */
    public function getParameters()
    {
        // TODO: Implement parameters() method.
    }

    /**
     * @return ControllerDispatcherContract
     */
    public function getControllerDispatcher()
    {
        if($this->container->isAttached(ControllerDispatcherContract::class)) {
            return $this->container->create(ControllerDispatcherContract::class);
        }

        return new ControllerDispatcher($this->container);
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        // TODO: Implement getDomain() method.
    }

    /**
     * @return string
     */
    public function getUri()
    {
        // TODO: Implement getUri() method.
    }

    /**
     * @return array
     */
    public function getConditionals()
    {
        // TODO: Implement getConditionals() method.
    }
}