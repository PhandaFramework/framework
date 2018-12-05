<?php

namespace Phanda\Contracts\Routing;

use Phanda\Contracts\Container\Container;
use Phanda\Foundation\Http\Request;
use Symfony\Component\Routing\CompiledRoute;

interface Route
{

    /**
     * Perform route action, return result.
     *
     * @return mixed
     */
    public function run();

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @param $domain
     * @return $this
     */
    public function setDomain($domain);

    /**
     * @return array
     */
    public function getRouteDefaults();

    /**
     * Set a default value for the route.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setRouteDefault($key, $value);

    /**
     * @return string
     */
    public function getUri();

    /**
     * @param string $uri
     * @return $this
     */
    public function setUri($uri);

    /**
     * @return array
     */
    public function getConditionals();

    /**
     * @param string $name
     * @param  string  $expression
     * @return mixed
     */
    public function condition($name, $expression = null);

    /**
     * @param Request $request
     * @return $this
     */
    public function bindToRequest(Request $request);

    /**
     * @return CompiledRoute
     */
    public function getSymfonyCompiledRoute();

    /**
     * Determine if the route has any parameters.
     *
     * @return bool
     */
    public function hasParameters();

    /**
     * Determine if a given parameter exists on the route.
     *
     * @param  string $name
     * @return bool
     */
    public function hasParameter($name);

    /**
     * Gets a given parameter from the route.
     *
     * @param  string  $name
     * @param  mixed   $default
     * @return string|object
     */
    public function getParameter($name, $default = null);

    /**
     * Set a parameter to the given value.
     *
     * @param  string  $name
     * @param  mixed   $value
     * @return void
     */
    public function setParameter($name, $value);

    /**
     * Unset a parameter on the route if it is set.
     *
     * @param  string  $name
     * @return void
     */
    public function removeParameter($name);

    /**
     * Get the key / value list of parameters without null values.
     *
     * @return array
     */
    public function getParametersWithoutNulls();

    /**
     * Get the key / value list of parameters for the route.
     *
     * @return array
     */
    public function getParameters();

    /**
     * @return array
     */
    public function getParameterNames();

    /**
     * @return array
     */
    public function getHttpMethods();

    /**
     * Determine if the route only responds to HTTP requests.
     *
     * @return bool
     */
    public function isHttpOnly();

    /**
     * Determine if the route only responds to HTTPS requests.
     *
     * @return bool
     */
    public function isHttpsOnly();

    /**
     * Get the name of the route instance.
     *
     * @return string
     */
    public function getName();

    /**
     * Add or change the route name.
     *
     * @param  string  $name
     * @return $this
     */
    public function setName($name);

    /**
     * Determine whether the route's name matches the given patterns.
     *
     * @param  mixed  ...$patterns
     * @return bool
     */
    public function isNamed(...$patterns);

    /**
     * Get the action name for the route.
     *
     * @return string
     */
    public function getMethodInvokerName();

    /**
     * Get the method name of the route action.
     *
     * @return string
     */
    public function getMethodName();

    /**
     * Get the action array or one of its properties for the route.
     *
     * @param  string|null $key
     * @return mixed
     */
    public function getAction($key = null);

    /**
     * Set the action array for the route.
     *
     * @param  array $action
     * @return $this
     */
    public function setActionArray(array $action);

    /**
     * @param Router $router
     * @return $this
     */
    public function setRouter(Router $router);

    /**
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container);

}