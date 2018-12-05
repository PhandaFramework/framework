<?php

namespace Phanda\Routing;

use Phanda\Contracts\Support\Repository;
use Phanda\Foundation\Http\Request;
use Phanda\Foundation\Http\Response;
use Phanda\Support\PhandArr;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Traversable;

class RouteRepository implements Repository, \Countable, \IteratorAggregate
{

    /** @var \Phanda\Contracts\Routing\Route[] */
    protected $routes = [];

    /**
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return PhandArr::has($this->routes, $key);
    }

    /**
     * @param  array|string $key
     * @param  mixed $default
     * @return \Phanda\Contracts\Routing\Route|\Phanda\Contracts\Routing\Route[]
     */
    public function get($key, $default = null)
    {
        return PhandArr::get($this->routes, $key, $default);
    }

    /**
     * Gets all routes by the method
     *
     * @param string|null $method
     * @return \Phanda\Contracts\Routing\Route[]
     */
    public function getByMethod($method = null)
    {
        if (is_null($method)) {
            return $this->all();
        }

        return PhandArr::filter($this->routes, function ($route) use ($method) {
            /** @var Route $route */
            return strtoupper($route->getMethodName()) == strtoupper($method);
        });
    }

    /**
     * @return \Phanda\Contracts\Routing\Route[]
     */
    public function all()
    {
        return $this->routes;
    }

    /**
     * @param  array|string $key
     * @param  \Phanda\Contracts\Routing\Route|null $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            PhandArr::set($this->routes, $key, $value);
        }
    }

    /**
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);
        array_unshift($array, $value);
        $this->set($key, $array);
    }

    /**
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key);
        $array[] = $value;
        $this->set($key, $array);
    }

    /**
     * @return Traversable|\Phanda\Contracts\Routing\Route[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->routes);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->routes);
    }

    /**
     * @param Request $request
     * @return Route
     *
     * @throws NotFoundHttpException
     */
    public function matchRequest(Request $request)
    {
        $routes = $this->getByMethod($request->getMethod());
        $route = $this->matchRoutesAgainstRequest($routes, $request);
        if(!is_null($route)) {
            return $route->bindToRequest($request);
        }

        $others = $this->checkForAlternateVerbs($request);
        if (count($others) > 0) {
            return $this->getRouteForMethods($request, $others);
        }

        throw new NotFoundHttpException();
    }

    /**
     * @param Route[] $routes
     * @param Request $request
     * @param bool $includingMethod
     * @return Route|null
     */
    protected function matchRoutesAgainstRequest(array $routes, Request $request, $includingMethod = true)
    {
        return PhandArr::first($routes, function($route) use ($request, $includingMethod) {
           /** @var Route $route */
           return $route->matchesRequest($request, $includingMethod);
        });
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function checkForAlternateVerbs(Request $request)
    {
        $methods = array_diff(Router::VERBS, [$request->getMethod()]);
        $others = [];

        foreach ($methods as $method) {
            if (! is_null($this->matchRoutesAgainstRequest($this->get($method), $request, false))) {
                $others[] = $method;
            }
        }

        return $others;
    }

    /**
     * @param Request $request
     * @param array $methods
     * @return Route
     */
    protected function getRouteForMethods(Request $request, array $methods)
    {
        if ($request->getMethod() === 'OPTIONS') {
            return (new Route('OPTIONS', $request->path(), function () use ($methods) {
                return new Response('', 200, ['Allow' => implode(',', $methods)]);
            }))->bindToRequest($request);
        }

        $this->methodNotAllowed($methods);
    }

    /**
     * @param array $others
     */
    protected function methodNotAllowed(array $others)
    {
        throw new MethodNotAllowedHttpException($others);
    }
}