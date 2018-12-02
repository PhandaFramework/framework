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
    protected function isAlias($abstract)
    {

    }

    protected function getAlias($abstract)
    {

    }

    /**
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function attach($abstract, $concrete = null, $shared = false)
    {
        // TODO: Implement attach() method.
    }

    /**
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        // TODO: Implement singleton() method.
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
        // TODO: Implement modify() method.
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
        if($this->isAlias($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        return isset($this->resolved[$abstract]) ||
            isset($this->instances[$abstract]);
    }
}