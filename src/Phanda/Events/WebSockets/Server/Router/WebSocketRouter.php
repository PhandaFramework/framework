<?php

namespace Phanda\Events\WebSockets\Server\Router;

use Phanda\Events\WebSockets\Handler;
use Ratchet\WebSocket\MessageComponentInterface;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class WebSocketRouter
{
	/**
	 * @var RouteCollection
	 */
	protected $routes;

	/**
	 * WebSocketRouter constructor.
	 */
	public function __construct()
	{
		$this->routes = new RouteCollection();
	}

	/**
	 * Register the websocket routes
	 */
	public function registerBaseRoutes()
	{
		$this->get('/app/{appKey}', Handler::class);
	}

	/**
	 * Gets the current route repository
	 *
	 * @return RouteCollection
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Register a new get route
	 *
	 * @param string $uri
	 * @param        $action
	 */
	public function get(string $uri, $action)
	{
		$this->addRoute('GET', $uri, $action);
	}

	/**
	 * Register a new post route
	 *
	 * @param string $uri
	 * @param        $action
	 */
	public function post(string $uri, $action)
	{
		$this->addRoute('POST', $uri, $action);
	}

	/**
	 * Register a new put route
	 *
	 * @param string $uri
	 * @param        $action
	 */
	public function put(string $uri, $action)
	{
		$this->addRoute('PUT', $uri, $action);
	}

	/**
	 * Register a new patch route
	 *
	 * @param string $uri
	 * @param        $action
	 */
	public function patch(string $uri, $action)
	{
		$this->addRoute('PATCH', $uri, $action);
	}

	/**
	 * Register a new delete route
	 *
	 * @param string $uri
	 * @param        $action
	 */
	public function delete(string $uri, $action)
	{
		$this->addRoute('DELETE', $uri, $action);
	}

	/**
	 * Adds a route to the route repository
	 *
	 * @param string $method
	 * @param string $uri
	 * @param        $action
	 */
	public function addRoute(string $method, string $uri, $action)
	{
		$this->routes->add($uri, $this->getRoute($method, $uri, $action));
	}

	/**
	 * Creates a new route from the given parameters
	 *
	 *
	 * @param string $method
	 * @param string $uri
	 * @param        $action
	 * @return Route
	 */
	protected function getRoute(string $method, string $uri, $action): Route
	{
		$action = is_subclass_of($action, MessageComponentInterface::class)
			? $this->createWebSocketsServer($action)
			: phanda()->create($action);

		return new Route($uri, ['_controller' => $action], [], [], null, [], [$method]);
	}

	/**
	 * Creates a new web socket server
	 *
	 * @param string $action
	 * @return WsServer
	 */
	protected function createWebSocketsServer(string $action): WsServer
	{
		$app = phanda()->create($action);
		return new WsServer($app);
	}
}