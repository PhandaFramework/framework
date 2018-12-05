<?php

namespace Phanda\Support\Routing;

use Phanda\Contracts\Routing\Route;
use Phanda\Foundation\Http\Request;
use Phanda\Support\PhandArr;

class RouteParameterBinder
{
    /**
     * @var Route
     */
    protected $route;

    /**
     * RouteParameterBinder constructor.
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getParameters(Request $request)
    {
        $parameters = $this->bindPathParameters($request);
        if (! is_null($this->route->getSymfonyCompiledRoute()->getHostRegex())) {
            $parameters = $this->bindHostParameters(
                $request, $parameters
            );
        }

        return $this->replaceDefaults($parameters);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function bindPathParameters(Request $request)
    {
        $path = '/'.ltrim($request->decodedPath(), '/');

        preg_match($this->route->getSymfonyCompiledRoute()->getRegex(), $path, $matches);

        return $this->matchToKeys(array_slice($matches, 1));
    }

    /**
     * @param Request $request
     * @param array $parameters
     * @return array
     */
    protected function bindHostParameters(Request $request, $parameters)
    {
        preg_match($this->route->getSymfonyCompiledRoute()->getHostRegex(), $request->getHost(), $matches);

        return array_merge($this->matchToKeys(array_slice($matches, 1)), $parameters);
    }

    /**
     * @param array $matches
     * @return array
     */
    protected function matchToKeys(array $matches)
    {
        if (empty($parameterNames = $this->route->getParameterNames())) {
            return [];
        }

        $parameters = array_intersect_key($matches, array_flip($parameterNames));

        return array_filter($parameters, function ($value) {
            return is_string($value) && strlen($value) > 0;
        });
    }

    /**
     * @param array $parameters
     * @return array
     */
    protected function replaceDefaults(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $value ?? PhandArr::get($this->route->getRouteDefaults(), $key);
        }

        foreach ($this->route->getRouteDefaults() as $key => $value) {
            if (! isset($parameters[$key])) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

}