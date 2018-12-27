<?php

namespace Phanda\Events\WebSockets\Server;

use Phanda\Events\WebSockets\Server\Router\OriginCheck;
use Phanda\Routing\RouteRepository;
use Ratchet\Http\Router;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;
use React\Socket\SecureServer;
use React\Socket\Server;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class ServerFactory
{
	/**
	 * The host to be running the websocket server on
	 *
	 * @var string
	 */
	protected $host = '127.0.0.1';

	/**
	 * The port to be running the websocket server on
	 *
	 * @var int
	 */
	protected $port = 8080;

	/**
	 * The loop interface
	 *
	 * @var LoopInterface
	 */
	protected $loop;

	/**
	 * The route repository
	 *
	 * @var RouteCollection
	 */
	protected $routes;

	/**
	 * The console output
	 *
	 * @var OutputInterface
	 */
	protected $consoleOutput;

	/**
	 * ServerFactory constructor.
	 */
	public function __construct()
	{
		$this->loop = LoopFactory::create();
	}

	/**
	 * @param string $host
	 * @return ServerFactory
	 */
	public function setHost(string $host): ServerFactory
	{
		$this->host = $host;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getHost(): string
	{
		return $this->host;
	}

	/**
	 * @param int $port
	 * @return ServerFactory
	 */
	public function setPort(int $port): ServerFactory
	{
		$this->port = $port;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPort(): int
	{
		return $this->port;
	}

	/**
	 * @param LoopInterface $loop
	 * @return ServerFactory
	 */
	public function setLoop(LoopInterface $loop): ServerFactory
	{
		$this->loop = $loop;
		return $this;
	}

	/**
	 * @return LoopInterface
	 */
	public function getLoop(): LoopInterface
	{
		return $this->loop;
	}

	/**
	 * @param RouteCollection $routes
	 * @return ServerFactory
	 */
	public function setRoutes(RouteCollection $routes): ServerFactory
	{
		$this->routes = $routes;
		return $this;
	}

	/**
	 * @return RouteCollection
	 */
	public function getRoutes(): RouteCollection
	{
		return $this->routes;
	}

	/**
	 * @param OutputInterface $consoleOutput
	 * @return ServerFactory
	 */
	public function setConsoleOutput(OutputInterface $consoleOutput): ServerFactory
	{
		$this->consoleOutput = $consoleOutput;
		return $this;
	}

	/**
	 * @return OutputInterface
	 */
	public function getConsoleOutput(): OutputInterface
	{
		return $this->consoleOutput;
	}

	/**
	 * @return IoServer
	 */
	public function createServer(): IoServer
	{
		$socket = new Server("{$this->host}:{$this->port}", $this->loop);

		if (config('websockets.ssl.local_cert')) {
			$socket = new SecureServer($socket, $this->loop, config('websockets.ssl'));
		}

		$urlMatcher = new UrlMatcher($this->getRoutes(), new RequestContext());
		$router = new Router($urlMatcher);
		$app = new OriginCheck($router, config('websockets.allowed_origins', []));
		$httpServer = new HttpServer($app, config('websockets.max_request_size_in_kb') * 1024);
		
		return new IoServer($httpServer, $socket, $this->loop);
	}

}