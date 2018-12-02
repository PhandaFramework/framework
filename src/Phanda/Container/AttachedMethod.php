<?php

namespace Phanda\Container;

use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;

class AttachedMethod
{
    /**
     * @param Container $container
     * @param callable|string $callback
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return mixed
     */
    public static function call($container, $callback, array $parameters = [], $defaultMethod = null)
    {
        if (static::isCallableWithAtSign($callback) || $defaultMethod) {
            return static::callClassAndMethod($container, $callback, $parameters, $defaultMethod);
        }

        return static::callAttachedMethod($container, $callback, function () use ($container, $callback, $parameters) {
            return $callback(static::getMethodDependencies($container, $callback, $parameters));
        });
    }

    /**
     * @param mixed $callback
     * @return bool
     */
    protected static function isCallableWithAtSign($callback)
    {
        return is_string($callback) && strpos($callback, '@') !== false;
    }

    /**
     * @param Container $container
     * @param string $target
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    protected static function callClassAndMethod($container, $target, array $parameters = [], $defaultMethod = null)
    {
        $segments = explode('@', $target);
        $method = count($segments) === 2
            ? $segments[1] : $defaultMethod;

        if (is_null($method)) {
            throw new InvalidArgumentException('No method provided to resolve.');
        }

        return static::call(
            $container, [$container->create($segments[0]), $method], $parameters
        );
    }

    /**
     * @param Container $container
     * @param callable $callback
     * @param mixed $default
     * @return mixed
     */
    protected static function callAttachedMethod($container, $callback, $default)
    {
        if (!is_array($callback)) {
            return $default instanceof Closure ? $default() : $default;
        }

        $method = static::normalizeMethod($callback);

        if ($container->hasMethodAttachment($method)) {
            return $container->callMethodAttachment($method, $callback[0]);
        }

        return $default instanceof Closure ? $default() : $default;
    }

    /**
     * @param callable $callback
     * @return string
     */
    protected static function normalizeMethod($callback)
    {
        $class = is_string($callback[0]) ? $callback[0] : get_class($callback[0]);

        return "{$class}@{$callback[1]}";
    }

    /**
     * @param Container $container
     * @param callable|string $callback
     * @param array $parameters
     * @return array
     *
     * @throws \ReflectionException
     */
    protected static function getMethodDependencies($container, $callback, array $parameters = [])
    {
        $dependencies = [];

        foreach (static::getCallReflector($callback)->getParameters() as $parameter) {
            static::addDependencyForCallParameter($container, $parameter, $parameters, $dependencies);
        }

        return array_merge($dependencies, $parameters);
    }

    /**
     * @param string|callable $callback
     * @return \ReflectionFunctionAbstract
     * @throws \ReflectionException
     */
    protected static function getCallReflector($callback)
    {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback);
        }

        return is_array($callback)
            ? new ReflectionMethod($callback[0], $callback[1])
            : new ReflectionFunction($callback);
    }

    /**
     * @param Container $container
     * @param \ReflectionParameter $parameter
     * @param array $parameters
     * @param array $dependencies
     * @return mixed
     */
    protected static function addDependencyForCallParameter($container, $parameter, array &$parameters, &$dependencies)
    {
        if (array_key_exists($parameter->name, $parameters)) {
            $dependencies[] = $parameters[$parameter->name];
            unset($parameters[$parameter->name]);
        } elseif ($parameter->getClass() && array_key_exists($parameter->getClass()->name, $parameters)) {
            $dependencies[] = $parameters[$parameter->getClass()->name];
            unset($parameters[$parameter->getClass()->name]);
        } elseif ($parameter->getClass()) {
            $dependencies[] = $container->create($parameter->getClass()->name);
        } elseif ($parameter->isDefaultValueAvailable()) {
            $dependencies[] = $parameter->getDefaultValue();
        }
    }
}