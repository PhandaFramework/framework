<?php

namespace Phanda\Util\Routing;

use Phanda\Contracts\Container\Container;
use Phanda\Support\PhandArr;
use ReflectionFunctionAbstract;
use ReflectionParameter;

/**
 * Trait ResolveRouteDependenciesTrait
 * @package Phanda\Util\Routing
 * @property Container $container
 */
trait ResolveRouteDependenciesTrait
{

    /**
     * @param array $parameters
     * @param object $instance
     * @param string $method
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function resolveClassMethodDependencies(array $parameters, $instance, $method)
    {
        if (!method_exists($instance, $method)) {
            return $parameters;
        }

        return $this->resolveMethodDependencies($parameters, new \ReflectionMethod($instance, $method));
    }

    /**
     * @param array $parameters
     * @param ReflectionFunctionAbstract $reflector
     * @return array
     */
    public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflector)
    {
        $instanceCount = 0;

        $values = array_values($parameters);

        foreach ($reflector->getParameters() as $key => $parameter) {
            $instance = $this->resolveDependencyAsClass(
                $parameter, $parameters
            );

            if (! is_null($instance)) {
                $instanceCount++;

                $this->convertToParameterList($parameters, $key, $instance);
            } elseif (! isset($values[$key - $instanceCount]) &&
                $parameter->isDefaultValueAvailable()) {
                $this->convertToParameterList($parameters, $key, $parameter->getDefaultValue());
            }
        }

        return $parameters;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param array $parameters
     * @return mixed
     */
    protected function resolveDependencyAsClass(ReflectionParameter $parameter, $parameters)
    {
        $class = $parameter->getClass();

        if ($class && !$this->alreadyInParameters($class->name, $parameters)) {
            return $parameter->isDefaultValueAvailable() ?
                $parameter->getDefaultValue() :
                $this->container->create($class->name);
        }
    }

    /**
     * @param string $class
     * @param array $parameters
     * @return bool
     */
    protected function alreadyInParameters($class, array $parameters)
    {
        return !is_null(PhandArr::first($parameters, function ($value) use ($class) {
            return $value instanceof $class;
        }));
    }

    /**
     * @param array $parameters
     * @param string $offset
     * @param mixed $value
     */
    protected function convertToParameterList(array &$parameters, $offset, $value)
    {
        array_splice(
            $parameters, $offset, 0, [$value]
        );
    }

}