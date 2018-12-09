<?php

namespace Phanda\Contracts\Container;

use Closure;
use Psr\Container\ContainerInterface as PsrContainer;

interface Container extends PsrContainer
{
    /**
     * @param string $id
     * @return mixed
     */
    public function get($id);

    /**
     * @param string $id
     * @return bool
     */
    public function has($id);

    /**
     * @param string $abstract
     * @return bool
     */
    public function isAttached($abstract);

    /**
     * @param string $abstract
     * @param string $alias
     * @return void
     */
    public function alias($abstract, $alias);

    /**
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function attach($abstract, $concrete = null, $shared = false);

    /**
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null);

    /**
     * @param string $abstract
     * @param Closure $closure
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function modify($abstract, Closure $closure);

    /**
     * @param string $abstract
     * @param mixed $instance
     * @return mixed
     */
    public function instance($abstract, $instance);

    /**
     * @param string $abstract
     */
    public function clearInstance($abstract);

    /**
     * @param string $abstract
     * @return Closure
     */
    public function getResolver($abstract);

    /**
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public function create($abstract, array $parameters = []);

    /**
     * @param callable|string $callback
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return mixed
     */
    public function call($callback, array $parameters = [], $defaultMethod = null);

    /**
     * @param string $abstract
     * @return mixed
     */
    public function isResolved($abstract);

    /**
     * @param string $abstract
     * @param Closure $callback
     * @return mixed
     */
    public function onReattach($abstract, Closure $callback);
}