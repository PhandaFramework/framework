<?php

namespace Phanda\Routing;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;

/**
 * @method Route get(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route post(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route put(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route delete(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route patch(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route options(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method Route any(string $name, string $uri, \Closure|array|string|null $action = null)
 * @method RouteRegistrar domain(string $value)
 * @method RouteRegistrar middleware(array|string|null $middleware)
 * @method RouteRegistrar name(string $value)
 * @method RouteRegistrar namespace(string $value)
 * @method RouteRegistrar prefix(string  $prefix)
 * @method RouteRegistrar where(array  $where)
 */
class RouteRegistrar
{
    /**
     * @var \Phanda\Contracts\Routing\Router
     */
    protected $router;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Methods that get passed through to the router
     *
     * @var array
     */
    protected $passToRouter = [
        'get', 'post', 'put', 'patch', 'delete', 'options', 'any',
    ];

    protected $allowedAttributes = [
        'domain', 'middleware', 'name', 'namespace', 'prefix', 'where',
    ];

    /**
     * RouteRegistrar constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function attribute($key, $value)
    {
        if (!in_array($key, $this->allowedAttributes)) {
            throw new InvalidArgumentException("Attribute [{$key}] does not exist.");
        }

        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param  \Closure|string $callback
     * @return void
     */
    public function group($callback)
    {
        $this->router->groupRoutes($this->attributes, $callback);
    }

    /**
     * @param $method
     * @param $uri
     * @param \Closure|array|string|null $action
     * @return mixed
     */
    protected function registerRoute($method, $uri, $action = null)
    {
        if (!is_array($action)) {
            $action = array_merge($this->attributes, $action ? ['uses' => $action] : []);
        }

        return $this->router->{$method}($uri, $this->compileAction($action));
    }

    /**
     * @param $action
     * @return array
     */
    protected function compileAction($action)
    {
        if (is_null($action)) {
            return $this->attributes;
        }

        if (is_string($action) || $action instanceof Closure) {
            $action = ['method' => $action];
        }

        return array_merge($this->attributes, $action);
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed|RouteRegistrar
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->passToRouter)) {
            return $this->registerRoute($method, ...$parameters);
        }

        if (in_array($method, $this->allowedAttributes)) {
            if ($method === 'middleware') {
                return $this->attribute($method, is_array($parameters[0]) ? $parameters[0] : $parameters);
            }

            return $this->attribute($method, $parameters[0]);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}