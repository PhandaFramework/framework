<?php

namespace Phanda\Routing\Controller;

use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Routing\Controller\Dispatcher as ControllerDispatcherContract;
use Phanda\Contracts\Routing\Route;
use Phanda\Util\Routing\ResolveRouteDependenciesTrait;

class Dispatcher implements ControllerDispatcherContract
{
    use ResolveRouteDependenciesTrait;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Dispatcher constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Route $route
     * @param AbstractController $controller
     * @param string $method
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public function dispatch(Route $route, AbstractController $controller, $method)
    {
        $parameters = $this->resolveClassMethodDependencies(
            $route->getParametersWithoutNulls(),
            $controller,
            $method
        );

        return $controller->callRouteMethod($method, $parameters);
    }
}