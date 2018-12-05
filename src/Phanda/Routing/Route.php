<?php

namespace Phanda\Routing;

use Closure;
use LogicException;
use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Routing\Route as RouteContract;
use Phanda\Contracts\Routing\Router;
use Phanda\Exceptions\Foundation\Http\HttpResponseException;
use Phanda\Foundation\Http\Request;
use Phanda\Routing\Controller\AbstractController;
use Phanda\Routing\Validators\ValidateAgainstHost;
use Phanda\Routing\Validators\ValidateAgainstMethod;
use Phanda\Routing\Validators\ValidateAgainstScheme;
use Phanda\Routing\Validators\ValidateAgainstUri;
use Phanda\Support\PhandArr;
use Phanda\Support\PhandaStr;
use Phanda\Support\Routing\RouteActionParser;
use Phanda\Support\Routing\RouteCompiler;
use Phanda\Support\Routing\RouteParameterBinder;
use Phanda\Util\Routing\ResolveRouteDependenciesTrait;
use ReflectionFunction;
use Symfony\Component\Routing\CompiledRoute;
use Phanda\Contracts\Routing\Controller\Dispatcher as ControllerDispatcherContract;
use Phanda\Routing\Controller\Dispatcher as ControllerDispatcher;

class Route implements RouteContract
{
    use ResolveRouteDependenciesTrait;

    /**
     * @var string
     */
    public $uri;

    /**
     * @var array
     */
    public $methods;

    /**
     * @var array
     */
    public $action;

    /**
     * @var AbstractController|null
     */
    public $controller;

    /**
     * @var array
     */
    public $defaults = [];

    /**
     * @var array
     */
    public $conditionals = [];

    /**
     * @var array
     */
    public $parameters;

    /**
     * @var array|null
     */
    public $parameterNames;

    /**
     * @var CompiledRoute
     */
    public $compiledRoute;

    /**
     * @var \Phanda\Contracts\Routing\Router
     */
    protected $router;

    /**
     * @var Container
     */
    protected $container;

    /**
     * The validators used by the routes to match against requests.
     *
     * @var array
     */
    public static $validators;

    /**
     * Route constructor.
     *
     * @param string $uri
     * @param array|string $methods
     * @param \Closure|array $action
     */
    public function __construct($uri, $methods, $action)
    {
        $this->uri = $uri;
        $this->methods = (array)$methods;
        $this->action = $this->parseAction($action);

        if (in_array('GET', $this->methods) && !in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
        }

        if (isset($this->action['prefix'])) {
            $this->setPrefix($this->action['prefix']);
        }
    }

    /**
     * @param  callable|array|null $action
     * @return array
     */
    protected function parseAction($action)
    {
        return RouteActionParser::parse($this->uri, $action);
    }

    /**
     * Perform route action, return result.
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public function run()
    {
        $this->container = $this->container ?: new \Phanda\Container\Container();

        try {
            if ($this->isActionOnController()) {
                return $this->runControllerMethod();
            }

            return $this->runCallableMethod();
        } catch (HttpResponseException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return bool
     */
    protected function isActionOnController()
    {
        return is_string($this->action['method']);
    }

    /**
     * @return mixed
     */
    protected function runControllerMethod()
    {
        return $this->getControllerDispatcher()->dispatch(
            $this,
            $this->getRouteController(),
            $this->getRouteControllerMethod()
        );
    }

    /**
     * @return AbstractController
     */
    public function getRouteController()
    {
        if (!$this->controller) {
            $class = $this->parseRouteControllerMethod()[0];
            $this->controller = $this->container->create(ltrim($class, '\\'));
        }

        return $this->controller;
    }

    /**
     * @return string
     */
    protected function getRouteControllerMethod()
    {
        return $this->parseRouteControllerMethod()[1];
    }

    /**
     * @return array
     */
    protected function parseRouteControllerMethod()
    {
        return PhandaStr::parseClassAtMethod($this->action['method']);
    }

    /**
     * @return mixed
     *
     * @throws \ReflectionException
     */
    protected function runCallableMethod()
    {
        $method = $this->action['method'];

        return $method(...array_values($this->resolveMethodDependencies(
            $this->getParametersWithoutNulls(), new ReflectionFunction($method)
        )));
    }

    /**
     * @param Request $request
     * @param bool $includingMethod
     * @return bool
     */
    public function matchesRequest(Request $request, $includingMethod = true)
    {
        $this->getSymfonyCompiledRoute();

        foreach ($this->getRequestValidators() as $validator) {
            if (! $includingMethod && $validator instanceof ValidateAgainstMethod) {
                continue;
            }

            if (! $validator->matches($this, $request)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the route validators for the instance.
     *
     * @return array
     */
    public static function getRequestValidators()
    {
        if (isset(static::$validators)) {
            return static::$validators;
        }

        // To match the route, we will use a chain of responsibility pattern with the
        // validator implementations. We will spin through each one making sure it
        // passes and then we will know if the route as a whole matches request.
        return static::$validators = [
            new ValidateAgainstUri(),
            new ValidateAgainstHost(),
            new ValidateAgainstScheme(),
            new ValidateAgainstMethod()
        ];
    }

    /**
     * @return CompiledRoute
     */
    public function getSymfonyCompiledRoute()
    {
        if (!$this->compiledRoute) {
            $this->compiledRoute = (new RouteCompiler($this))->compile();
        }

        return $this->compiledRoute;
    }

    /**
     * @return ControllerDispatcherContract
     */
    public function getControllerDispatcher()
    {
        if ($this->container->isAttached(ControllerDispatcherContract::class)) {
            return $this->container->create(ControllerDispatcherContract::class);
        }

        return new ControllerDispatcher($this->container);
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return isset($this->action['domain'])
            ? str_replace(['http://', 'https://'], '', $this->action['domain']) : null;
    }

    /**
     * @param $domain
     * @return RouteContract
     */
    public function setDomain($domain)
    {
        $this->action['domain'] = $domain;
        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return array
     */
    public function getConditionals()
    {
        return $this->conditionals;
    }

    /**
     * @return array
     */
    public function getRouteDefaults()
    {
        return $this->defaults;
    }

    /**
     * Set a default value for the route.
     *
     * @param  string $key
     * @param  mixed $value
     * @return $this
     */
    public function setRouteDefault($key, $value)
    {
        $this->defaults[$key] = $value;
        return $this;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function bindToRequest(Request $request)
    {
        $this->getSymfonyCompiledRoute();

        $this->parameters = (new RouteParameterBinder($this))
            ->getParameters($request);

        return $this;
    }

    /**
     * Determine if the route has any parameters.
     *
     * @return bool
     */
    public function hasParameters()
    {
        return isset($this->parameters) && count($this->parameters) > 0;
    }

    /**
     * Determine if a given parameter exists on the route.
     *
     * @param  string $name
     * @return bool
     */
    public function hasParameter($name)
    {
        if ($this->hasParameters()) {
            return array_key_exists($name, $this->getParameters());
        }

        return false;
    }

    /**
     * Gets a given parameter from the route.
     *
     * @param  string $name
     * @param  mixed $default
     * @return string|object
     */
    public function getParameter($name, $default = null)
    {
        return PhandArr::get($this->getParameters(), $name, $default);
    }

    /**
     * Set a parameter to the given value.
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function setParameter($name, $value)
    {
        $this->getParameters();
        $this->parameters[$name] = $value;
    }

    /**
     * Unset a parameter on the route if it is set.
     *
     * @param  string $name
     * @return void
     */
    public function removeParameter($name)
    {
        $this->getParameters();
        unset($this->parameters[$name]);
    }

    /**
     * Get the key / value list of parameters without null values.
     *
     * @return array
     */
    public function getParametersWithoutNulls()
    {
        return array_filter($this->getParameters(), function ($p) {
            return !is_null($p);
        });
    }

    /**
     * Get the key / value list of parameters for the route.
     *
     * @return array
     */
    public function getParameters()
    {
        if (isset($this->parameters)) {
            return $this->parameters;
        }

        throw new LogicException('Route is not bound, or has no parameters.');
    }

    /**
     * @return array
     */
    public function getParameterNames()
    {
        if (isset($this->parameterNames)) {
            return $this->parameterNames;
        }

        return $this->parameterNames = $this->compileParameterNames();
    }

    /**
     * Get the parameter names for the route.
     *
     * @return array
     */
    protected function compileParameterNames()
    {
        preg_match_all('/\{(.*?)\}/', $this->getDomain() . $this->uri, $matches);

        return array_map(function ($m) {
            return trim($m, '?');
        }, $matches[1]);
    }

    /**
     * @param string|array $name
     * @param  string $expression
     * @return mixed
     */
    public function condition($name, $expression = null)
    {
        foreach ($this->parseCondition($name, $expression) as $name => $expression) {
            $this->conditionals[$name] = $expression;
        }

        return $this;
    }

    /**
     * Parse arguments to the where method into an array.
     *
     * @param  array|string $name
     * @param  string $expression
     * @return array
     */
    protected function parseCondition($name, $expression)
    {
        return is_array($name) ? $name : [$name => $expression];
    }

    /**
     * @return array
     */
    public function getHttpMethods()
    {
        return $this->methods;
    }

    /**
     * Determine if the route only responds to HTTP requests.
     *
     * @return bool
     */
    public function isHttpOnly()
    {
        return in_array('http', $this->action, true);
    }

    /**
     * Determine if the route only responds to HTTPS requests.
     *
     * @return bool
     */
    public function isHttpsOnly()
    {
        return in_array('https', $this->action, true);
    }

    /**
     * Get the name of the route instance.
     *
     * @return string
     */
    public function getName()
    {
        return $this->action['name'] ?? null;
    }

    /**
     * Add or change the route name.
     *
     * @param  string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->action['name'] = isset($this->action['name']) ? $this->action['name'] . $name : $name;

        return $this;
    }

    /**
     * Determine whether the route's name matches the given patterns.
     *
     * @param  mixed ...$patterns
     * @return bool
     */
    public function isNamed(...$patterns)
    {
        if (is_null($routeName = $this->getName())) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (PhandaStr::matchesPattern($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Closure|string $action
     * @return mixed
     */
    public function setMethod($action)
    {
        $action = is_string($action) ? $this->addGroupNamespaceToStringUses($action) : $action;

        return $this->setActionArray(array_merge($this->action, $this->parseAction([
            'method' => $action,
            'controller' => $action,
        ])));
    }

    /**
     * Parse a string based action for the "uses" fluent method.
     *
     * @param  string $action
     * @return string
     */
    protected function addGroupNamespaceToStringUses($action)
    {
        // fix this when groups work :eye_roll:
        /**$groupStack = end($this->router->getGroupStack());
         *
         * if (isset($groupStack['namespace']) && strpos($action, '\\') !== 0) {
         * return $groupStack['namespace'].'\\'.$action;
         * }*/

        return $action;
    }

    /**
     * Get the action name for the route.
     *
     * @return string
     */
    public function getMethodInvokerName()
    {
        return $this->action['controller'] ?? 'Closure';
    }

    /**
     * Get the method name of the route action.
     *
     * @return string
     */
    public function getMethodName()
    {
        return PhandArr::last(explode('@', $this->getMethodInvokerName()));
    }

    /**
     * Get the action array or one of its properties for the route.
     *
     * @param  string|null $key
     * @return mixed
     */
    public function getAction($key = null)
    {
        return PhandArr::get($this->action, $key);
    }

    /**
     * Set the action array for the route.
     *
     * @param  array $action
     * @return $this
     */
    public function setActionArray(array $action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get the prefix of the route instance.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->action['prefix'] ?? null;
    }

    /**
     * Add a prefix to the route URI.
     *
     * @param  string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $uri = rtrim($prefix, '/') . '/' . ltrim($this->uri, '/');
        $this->uri = trim($uri, '/');
        return $this;
    }

    /**
     * @param Router $router
     * @return RouteContract
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @param Container $container
     * @return RouteContract
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Dynamically access route parameters.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getParameter($key);
    }
}