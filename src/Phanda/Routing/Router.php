<?php

namespace Phanda\Routing;

use ArrayObject;
use JsonSerializable;
use Phanda\Conduit\HttpConduit;
use Phanda\Contracts\Events\Dispatcher;
use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Routing\Route;
use Phanda\Contracts\Routing\Router as RouterContract;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Contracts\Support\Jsonable;
use Phanda\Contracts\Support\Responsable;
use Phanda\Foundation\Http\Request;
use Phanda\Foundation\Http\Response;
use Phanda\Http\JsonResponse;
use Phanda\Routing\Events\PreparingResponse;
use Phanda\Support\Routing\RouteGroupMerger;
use Phanda\Routing\Route as PhandaRoute;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @mixin RouteRegistrar
 */
class Router implements RouterContract
{
    public const VERBS = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * @var Dispatcher
     */
    protected $eventDispatcher;

    /**
     * @var Application
     */
    protected $phanda;

    /**
     * The route collection instance.
     *
     * @var RouteRepository
     */
    protected $routes;

    /**
     * @var Route
     */
    protected $currentRoute;

    /**
     * @var Request
     */
    protected $currentRequest;

    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * The globally available parameter patterns.
     *
     * @var array
     */
    protected $patterns = [];

    /**
     * Router constructor.
     * @param Dispatcher $eventDispatcher
     * @param Application $phanda
     */
    public function __construct(Dispatcher $eventDispatcher, Application $phanda)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->phanda = $phanda;
        $this->routes = new RouteRepository();
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @param string|null $name
     * @return Route
     */
    public function get($uri, $action, $name = null)
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action, $name);
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @param string|null $name
     * @return Route
     */
    public function post($uri, $action, $name = null)
    {
        return $this->addRoute('POST', $uri, $action, $name);
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @param string|null $name
     * @return Route
     */
    public function put($uri, $action, $name = null)
    {
        return $this->addRoute('PUT', $uri, $action, $name);
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @param string|null $name
     * @return Route
     */
    public function delete($uri, $action, $name = null)
    {
        return $this->addRoute('DELETE', $uri, $action, $name);
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @param string|null $name
     * @return Route
     */
    public function patch($uri, $action, $name = null)
    {
        return $this->addRoute('PATCH', $uri, $action, $name);
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @param string|null $name
     * @return Route
     */
    public function options($uri, $action, $name = null)
    {
        return $this->addRoute('OPTIONS', $uri, $action, $name);
    }

    /**
     * @param string $uri
     * @param \Closure|array|string|callable $action
     * @param string|null $name
     * @return Route
     */
    public function any($uri, $action, $name = null)
    {
        return $this->addRoute(self::VERBS, $uri, $action, $name);
    }

    /**
     * @param string $name
     * @param array|string $methods
     * @param string $uri
     * @param \Closure|array|string|callable|null $action
     * @return Route
     */
    public function addRoute($methods, $uri, $action, $name = null)
    {
        $this->routes->set($name ?? $uri, $route = $this->createRoute($methods, $uri, $action, $name));
        return $route;
    }

    /**
     * @param $name
     * @param $methods
     * @param $uri
     * @param $action
     * @return Route
     */
    protected function createRoute($methods, $uri, $action, $name = null)
    {
        if ($this->isActionInController($action)) {
            $action = $this->convertActionToControllerAction($action);
        }

        $route = $this->newPhandaRoute(
            $methods,
            $this->setPrefix($uri),
            $action,
            $name
        );

        if ($this->hasGroupStack()) {
            $this->mergeGroupAttributesIntoRoute($route);
        }

        $this->addWhereClausesToRoute($route);

        return $route;
    }

    /**
     * @param array $action
     * @return bool
     */
    protected function isActionInController($action)
    {
        if (!$action instanceof \Closure && !is_array($action) && !$action instanceof Arrayable) {
            return is_string($action) || (isset($action['method']) && is_string($action['method']));
        }

        return false;
    }

    /**
     * @param array|string $action
     * @return array
     */
    protected function convertActionToControllerAction($action)
    {
        if (is_string($action)) {
            $action = ['method' => $action];
        }

        if (!empty($this->groupStack)) {
            $action['method'] = $this->prependGroupNamespace($action['method']);
        }

        $action['controller'] = $action['method'];

        return $action;
    }

    /**
     * Prepend the last group namespace onto the use clause.
     *
     * @param  string $class
     * @return string
     */
    protected function prependGroupNamespace($class)
    {
        $group = end($this->groupStack);
        return isset($group['namespace']) && strpos($class, '\\') !== 0
            ? $group['namespace'] . '\\' . $class : $class;
    }

    /**
     * @param array $attributes
     * @param \Closure|string $routes
     */
    public function groupRoutes(array $attributes, $routes)
    {
        $this->updateGroupStack($attributes);
        $this->loadRoutes($routes);
        array_pop($this->groupStack);
    }

    /**
     * Determine if the router currently has a group stack.
     *
     * @return bool
     */
    public function hasGroupStack()
    {
        return !empty($this->groupStack);
    }

    /**
     * @param array $attributes
     */
    protected function updateGroupStack(array $attributes)
    {
        if (!empty($this->groupStack)) {
            $attributes = $this->mergeGroupWithLast($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * @param array $newGroup
     * @return array
     */
    protected function mergeGroupWithLast($newGroup)
    {
        return RouteGroupMerger::merge($newGroup, end($this->groupStack));
    }

    /**
     * @param \Closure|string $routes
     */
    protected function loadRoutes($routes)
    {
        if ($routes instanceof \Closure) {
            $routes($this);
        } else {
            $router = $this;
            require $routes;
        }
    }

    /**
     * @return string
     */
    protected function getLastGroupPrefix()
    {
        if (!empty($this->groupStack)) {
            $last = end($this->groupStack);

            return $last['prefix'] ?? '';
        }

        return '';
    }

    /**
     * @param $methods
     * @param $uri
     * @param $action
     * @param string|null $name
     * @return Route
     */
    protected function newPhandaRoute($methods, $uri, $action, $name = null)
    {
        return (new PhandaRoute($uri, $methods, $action, $name))
            ->setRouter($this)
            ->setContainer($this->phanda);
    }

    /**
     * @param Route $route
     * @return Route
     */
    protected function addWhereClausesToRoute(Route $route)
    {
        $route->condition(array_merge(
            $this->patterns, $route->getAction()['where'] ?? []
        ));

        return $route;
    }

    /**
     * Merge the group stack with the controller action.
     *
     * @param  Route $route
     * @return void
     */
    protected function mergeGroupAttributesIntoRoute($route)
    {
        $route->setActionArray($this->mergeGroupWithLast($route->getAction()));
    }

    /**
     * @param  string  $uri
     * @return string
     */
    protected function setPrefix($uri)
    {
        return trim(trim($this->getLastGroupPrefix(), '/').'/'.trim($uri, '/'), '/') ?: '/';
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function dispatch(Request $request)
    {
        $this->currentRequest = $request;
        return $this->dispatchToRoute($request);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function dispatchToRoute(Request $request)
    {
        return $this->runRoute($request, $this->findRoute($request));
    }

    /**
     * @param $request
     * @return Route
     */
    protected function findRoute($request)
    {
        $this->currentRoute = $route = $this->routes->matchRequest($request);

        $this->phanda->instance(Route::class, $route);

        return $route;
    }

    /**
     * @param Request $request
     * @param Route $route
     * @return Response|JsonResponse
     */
    protected function runRoute(Request $request, Route $route)
    {
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $this->eventDispatcher->dispatch('preparingRouteResponse', new PreparingResponse($route, $request));

        return $this->prepareResponse($request,
            $this->runRouteWithinStack($route, $request)
        );
    }

    /**
     * @param $request
     * @param $response
     * @return Response|JsonResponse
     */
    public function prepareResponse($request, $response)
    {
        return static::toResponse($request, $response);
    }

    /**
     * @param $request
     * @param $response
     * @return Response|JsonResponse
     */
    public static function toResponse($request, $response)
    {
        if ($response instanceof Responsable) {
            $response = $response->toResponse($request);
        }

        if (! $response instanceof SymfonyResponse &&
            ($response instanceof Arrayable ||
                $response instanceof Jsonable ||
                $response instanceof ArrayObject ||
                $response instanceof JsonSerializable ||
                is_array($response))) {
            $response = new JsonResponse($response);
        } elseif (! $response instanceof SymfonyResponse) {
            $response = new Response($response);
        }

        if ($response->getStatusCode() === Response::HTTP_NOT_MODIFIED) {
            $response->setNotModified();
        }

        return $response->prepare($request);
    }

    /**
     * @param Route $route
     * @param Request $request
     * @return mixed
     */
    protected function runRouteWithinStack(Route $route, Request $request)
    {
        return (new HttpConduit($this->phanda))
            ->send($request)
            ->then(function ($request) use ($route) {
                return $this->prepareResponse(
                    $request, $route->run()
                );
            });
    }

    /**
     * Get the underlying route repository.
     *
     * @return RouteRepository
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @return array
     */
    public function getGroupStack()
    {
        return $this->groupStack;
    }

    /**
     * Dynamically handle calls into the router instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return (new RouteRegistrar($this))->attribute($method, $parameters[0]);
    }

    /**
     * @return Route
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }
}