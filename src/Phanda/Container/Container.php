<?php

namespace Phanda\Container;

use Closure;
use Phanda\Contracts\Container\Container as ContainerContract;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerContract
{
    /**
     * Current registered global container
     *
     * @var static
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $resolved = [];

    /**
     * @var array
     */
    protected $attachments = [];

    /**
     * @var array
     */
    protected $methodAttachments = [];

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * @var array
     */
    protected $abstractAliases = [];

    /**
     * @var array
     */
    protected $modifiers = [];

    /**
     * @var array
     */
    protected $buildQueue = [];

    /**
     * @var array
     */
    protected $reboundCallbacks = [];

    /**
     * @var array
     */
    public $contextualAttachments = [];

    /**
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        // TODO: Implement get() method.
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return $this->isAttached($id);
    }

    /**
     * @param string $abstract
     * @return bool
     */
    public function isAttached($abstract)
    {
        return isset($this->attachments[$abstract]) ||
            isset($this->instances[$abstract]) ||
            $this->isAlias($abstract);
    }

    /**
     * @param string $abstract
     * @param string $alias
     * @return void
     */
    public function alias($abstract, $alias)
    {
        // TODO: Implement alias() method.
    }

    /**
     * @param $abstract
     * @return bool
     */
    public function isAlias($abstract)
    {
        return isset($this->aliases[$abstract]);
    }

    /**
     * @param $abstract
     * @return mixed
     */
    public function getAlias($abstract)
    {

    }

    public function isSharedAttachment($abstract)
    {
        return isset($this->instances[$abstract]) ||
            isset($this->attachments[$abstract]['shared']) &&
            $this->attachments[$abstract]['shared'] === true;
    }

    /**
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function attach($abstract, $concrete = null, $shared = false)
    {
        $this->clearOldInstances($abstract);

        if ($concrete === null) {
            $concrete = $abstract;
        }

        if (!$concrete instanceof Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->attachments[$abstract] = compact('concrete', 'shared');

        if ($this->isResolved($abstract)) {
            $this->reattached($abstract);
        }
    }

    protected function clearOldInstances($abstract)
    {

    }

    /**
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @return Closure
     */
    protected function getClosure($abstract, $concrete)
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            /** @var Container $container */
            if ($abstract == $concrete) {
                return $container->build($concrete);
            }

            return $container->create($concrete, $parameters);
        };
    }

    /**
     * @param string $method
     * @return bool
     */
    public function hasMethodAttachment($method)
    {
        return isset($this->methodAttachments[$method]);
    }

    /**
     * @param string|array $method
     * @param Closure $callback
     */
    public function attachMethod($method, $callback)
    {
        $this->methodAttachments[$this->parseMethodAttachment($method)] = $callback;
    }

    /**
     * @param string|array $method
     * @return string
     */
    public function parseMethodAttachment($method)
    {
        if(is_array($method)) {
            return $method[0] . '@' . $method[1];
        }

        return $method;
    }

    /**
     * @param string $method
     * @param mixed $instance
     * @return mixed
     */
    public function callMethodAttachment($method, $instance)
    {
        return $this->methodAttachments[$method]($instance, $this);
    }

    /**
     * @param string $concrete
     * @param string $abstract
     * @param Closure|string $implementation
     */
    public function addContextualAttachment($concrete, $abstract, $implementation)
    {
        $this->contextualAttachments[$concrete][$this->getAlias($abstract)] = $implementation;
    }

    /**
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->attach($abstract, $concrete, true);
    }

    /**
     * @param string $abstract
     * @param Closure $closure
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function modify($abstract, Closure $closure)
    {
        $abstract = $this->getAlias($abstract);

        if(isset($this->instances[$abstract])) {
            $this->instances[$abstract] = $closure($this->instances[$abstract], $this);

            $this->reattached($abstract);
        } else {
            $this->modifiers[$abstract][] = $closure;

            if($this->isResolved($abstract)) {
                $this->reattached($abstract);
            }
        }
    }

    /**
     * @param string $abstract
     * @param mixed $instance
     * @return mixed
     */
    public function instance($abstract, $instance)
    {
        // TODO: Implement instance() method.
    }

    /**
     * @param string $abstract
     * @return Closure
     */
    public function getResolver($abstract)
    {
        // TODO: Implement getResolver() method.
    }

    /**
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public function create($abstract, array $parameters = [])
    {
        // TODO: Implement create() method.
    }

    public function build($concrete)
    {

    }

    /**
     * @param callable|string $callback
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return mixed
     */
    public function call($callback, array $parameters = [], $defaultMethod = null)
    {
        // TODO: Implement call() method.
    }

    /**
     * @param string $abstract
     * @return mixed
     */
    public function isResolved($abstract)
    {
        if ($this->isAlias($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        return isset($this->resolved[$abstract]) ||
            isset($this->instances[$abstract]);
    }

    protected function reattached($abstract)
    {

    }
}