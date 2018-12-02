<?php

namespace Phanda\Container;

use Closure;
use LogicException;
use Phanda\Contracts\Container\Container as ContainerContract;
use Phanda\Exceptions\Container\ContainerEntryNotFoundException;
use Phanda\Exceptions\Container\ResolvingAttachmentException;
use ReflectionClass;
use ReflectionParameter;

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
    protected $reattachedCallbacks = [];

    /**
     * @var array
     */
    protected $with = [];

    /**
     * @var array
     */
    public $contextualAttachments = [];

    /**
     * @param string $id
     * @return mixed
     * @throws \Exception
     */
    public function get($id)
    {
        try {
            return $this->resolve($id);
        } catch (\Exception $e) {
            if ($this->has($id)) {
                throw $e;
            }

            throw new ContainerEntryNotFoundException;
        }
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
        $this->aliases[$alias] = $abstract;

        $this->abstractAliases[$abstract][] = $alias;
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
     * @param string $abstract
     * @return string
     */
    public function getAlias($abstract)
    {
        if (! isset($this->aliases[$abstract])) {
            return $abstract;
        }

        if ($this->aliases[$abstract] === $abstract) {
            throw new LogicException("[{$abstract}] is aliased to itself.");
        }

        return $this->getAlias($this->aliases[$abstract]);
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
     * @throws \ReflectionException
     * @throws ResolvingAttachmentException
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

    /**
     * @param string $abstract
     */
    protected function clearOldInstances($abstract)
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);
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
        if (is_array($method)) {
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
     * @throws \ReflectionException
     * @throws ResolvingAttachmentException
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
     * @throws \ReflectionException
     * @throws ResolvingAttachmentException
     */
    public function modify($abstract, Closure $closure)
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract])) {
            $this->instances[$abstract] = $closure($this->instances[$abstract], $this);

            $this->reattached($abstract);
        } else {
            $this->modifiers[$abstract][] = $closure;

            if ($this->isResolved($abstract)) {
                $this->reattached($abstract);
            }
        }
    }

    /**
     * @param string $abstract
     * @param mixed $instance
     * @return mixed
     * @throws \ReflectionException
     * @throws ResolvingAttachmentException
     */
    public function instance($abstract, $instance)
    {
        $this->clearAbstractAliases($abstract);

        $isAttached = $this->isAttached($abstract);

        unset($this->aliases[$abstract]);

        $this->instances[$abstract] = $instance;

        if ($isAttached) {
            $this->reattached($abstract);
        }

        return $instance;
    }

    /**
     * @param string $abstract
     */
    protected function clearAbstractAliases($abstract)
    {
        if (!isset($this->aliases[$abstract])) {
            return;
        }

        foreach ($this->abstractAliases as $abstract => $aliases) {
            foreach ($aliases as $index => $alias) {
                if ($alias == $abstract) {
                    unset($this->abstractAliases[$abstract][$index]);
                }
            }
        }
    }

    /**
     * @param string $abstract
     * @return Closure
     */
    public function getResolver($abstract)
    {
        return function () use ($abstract) {
            return $this->create($abstract);
        };
    }

    /**
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     * @throws ResolvingAttachmentException
     */
    public function create($abstract, array $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * @param string $concrete
     * @return mixed
     * @throws \ReflectionException
     * @throws ResolvingAttachmentException
     */
    public function build($concrete)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $this->getLastParameterOverride());
        }

        $reflector = new ReflectionClass($concrete);

        if (! $reflector->isInstantiable()) {
            return $this->notInstantiable($concrete);
        }

        $this->buildQueue[] = $concrete;

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            array_pop($this->buildQueue);

            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instances = $this->resolveDependencies(
            $dependencies
        );

        array_pop($this->buildQueue);
        return $reflector->newInstanceArgs($instances);
    }

    /**
     * @param callable|string $callback
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return mixed
     */
    public function call($callback, array $parameters = [], $defaultMethod = null)
    {
        return AttachedMethod::call($this, $callback, $parameters, $defaultMethod);
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

    /**
     * @param string $abstract
     * @throws \ReflectionException
     * @throws ResolvingAttachmentException
     */
    protected function reattached($abstract)
    {
        $instance = $this->create($abstract);

        foreach ($this->getReattachedCallbacks($abstract) as $callback) {
            $callback($this, $instance);
        }
    }

    /**
     * @param string $abstract
     * @return array
     */
    protected function getReattachedCallbacks($abstract)
    {
        if (isset($this->reattachedCallbacks[$abstract])) {
            return $this->reattachedCallbacks[$abstract];
        }

        return [];
    }

    /**
     * @param Closure $callback
     * @param array $parameters
     * @return Closure
     */
    public function wrapFunction(Closure $callback, array $parameters = [])
    {
        return function () use ($callback, $parameters) {
            return $this->call($callback, $parameters);
        };
    }

    /**
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     * @throws ResolvingAttachmentException
     */
    protected function resolve($abstract, $parameters = [])
    {
        $abstract = $this->getAlias($abstract);
        $needsContextualBuild = !empty($parameters) || !is_null($this->getContextualConcrete($abstract));

        if (isset($this->instances[$abstract]) && !$needsContextualBuild) {
            return $this->instances[$abstract];
        }

        $this->with[] = $parameters;
        $concrete = $this->getConcrete($abstract);

        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete);
        } else {
            $object = $this->create($concrete);
        }

        foreach ($this->getModifiers($abstract) as $modifier) {
            $object = $modifier($object, $this);
        }

        if ($this->isSharedAttachment($abstract) && !$needsContextualBuild) {
            $this->instances[$abstract] = $object;
        }

        $this->resolved[$abstract] = true;
        array_pop($this->with);

        return $object;
    }

    /**
     * @param string $abstract
     * @return mixed
     */
    protected function getConcrete($abstract)
    {
        if (!is_null($concrete = $this->getContextualConcrete($abstract))) {
            return $concrete;
        }

        if (isset($this->attachments[$abstract])) {
            return $this->attachments[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * @param string $abstract
     * @return string|null
     */
    protected function getContextualConcrete($abstract)
    {
        if (! is_null($binding = $this->findInContextualAttachments($abstract))) {
            return $binding;
        }

        if (empty($this->abstractAliases[$abstract])) {
            return null;
        }

        foreach ($this->abstractAliases[$abstract] as $alias) {
            if (! is_null($binding = $this->findInContextualAttachments($alias))) {
                return $binding;
            }
        }

        return null;
    }

    /**
     * @param string $abstract
     * @return string|null
     */
    protected function findInContextualAttachments($abstract)
    {
        if (isset($this->contextualAttachments[end($this->buildQueue)][$abstract])) {
            return $this->contextualAttachments[end($this->buildQueue)][$abstract];
        }

        return null;
    }

    /**
     * @param mixed $concrete
     * @param string $abstract
     * @return bool
     */
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    /**
     * @param array $dependencies
     * @return array
     * @throws ResolvingAttachmentException
     * @throws \ReflectionException
     */
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            if ($this->hasParameterOverride($dependency)) {
                $results[] = $this->getParameterOverride($dependency);

                continue;
            }

            $results[] = is_null($dependency->getClass())
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);
        }

        return $results;
    }

    /**
     * @param ReflectionParameter $dependency
     * @return bool
     */
    protected function hasParameterOverride($dependency)
    {
        return array_key_exists(
            $dependency->name, $this->getLastParameterOverride()
        );
    }

    /**
     * @param ReflectionParameter $dependency
     * @return mixed
     */
    protected function getParameterOverride($dependency)
    {
        return $this->getLastParameterOverride()[$dependency->name];
    }

    /**
     * @return array
     */
    protected function getLastParameterOverride()
    {
        return count($this->with) ? end($this->with) : [];
    }

    /**
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws ResolvingAttachmentException
     */
    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        if (! is_null($concrete = $this->getContextualConcrete('$'.$parameter->name))) {
            return $concrete instanceof Closure ? $concrete($this) : $concrete;
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $this->unresolvablePrimitive($parameter);
        return null;
    }

    /**
     * @param ReflectionParameter $parameter
     * @return mixed
     *
     * @throws ResolvingAttachmentException
     * @throws \ReflectionException
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->create($parameter->getClass()->name);
        } catch (ResolvingAttachmentException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

    /**
     * @param string $concrete
     * @throws ResolvingAttachmentException
     */
    protected function notInstantiable($concrete)
    {
        if (! empty($this->buildQueue)) {
            $previous = implode(', ', $this->buildQueue);

            $message = "Target [$concrete] is not instantiable while building [$previous].";
        } else {
            $message = "Target [$concrete] is not instantiable.";
        }

        throw new ResolvingAttachmentException($message);
    }

    /**
     * @param ReflectionParameter $parameter
     * @throws ResolvingAttachmentException
     */
    protected function unresolvablePrimitive(ReflectionParameter $parameter)
    {
        $message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";

        throw new ResolvingAttachmentException($message);
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param string $abstract
     * @return array
     */
    protected function getModifiers($abstract)
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->modifiers[$abstract])) {
            return $this->modifiers[$abstract];
        }

        return [];
    }

    /**
     * @param string $abstract
     */
    public function clearModifiers($abstract)
    {
        unset($this->modifiers[$this->getAlias($abstract)]);
    }

    /**
     * Cleans all modifiers
     */
    public function clearAllModifiers()
    {
        $this->modifiers = [];
    }

    /**
     * @param string $abstract
     */
    public function clearInstance($abstract)
    {
        unset($this->instances[$abstract]);
    }

    /**
     * Cleans all instances
     */
    public function clearInstances()
    {
        $this->instances = [];
    }

    /**
     * Resets all objects on container
     */
    public function reset()
    {
        $this->aliases = [];
        $this->resolved = [];
        $this->attachments = [];
        $this->instances = [];
        $this->abstractAliases = [];
    }

    /**
     * @return Container
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * @param ContainerContract|null $container
     * @return ContainerContract
     */
    public static function setInstance(ContainerContract $container = null)
    {
        return static::$instance = $container;
    }

    /**
     * Dynamically access container services.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this[$key];
    }

    /**
     * Dynamically set container services.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }
}