<?php

namespace Phanda\Events\WebSockets\Commands;

use Phanda\Console\ConsoleCommand;
use Phanda\Events\WebSockets\Server\Router\WebSocketRouter;
use Phanda\Events\WebSockets\Server\ServerFactory;
use React\EventLoop\Factory as LoopFactory;

class StartWebSocketServer extends ConsoleCommand
{
	protected $signature = 'websockets:serve {--host=127.0.0.1} {--port=7001}';

	protected $description = 'Start the Phanda WebSocket Server';

	/** @var \React\EventLoop\LoopInterface */
	protected $loop;

	/**
	 * @var WebSocketRouter
	 */
	protected $webSocketRouter;

	public function __construct()
	{
		parent::__construct();

		$this->loop = LoopFactory::create();
		$this->webSocketRouter = phanda()->create(WebSocketRouter::class);
	}

	public function handle()
	{
		$this->registerRoutes()
			->startSocketServer();
	}

	/**
	 * Register the base routes of the socket server
	 *
	 * @return $this
	 */
	protected function registerRoutes()
	{
		$this->webSocketRouter->registerBaseRoutes();
		return $this;
	}

	/**
	 * Starts the socket server
	 *
	 * @return $this
	 */
	protected function startSocketServer()
	{
		$this->info("Starting the WebSocket server on {$this->getOption('host')}:{$this->getOption('port')}...");

		$routes = $this->webSocketRouter->getRoutes();

		(new ServerFactory())
			->setLoop($this->loop)
			->setRoutes($routes)
			->setHost($this->getOption('host'))
			->setPort($this->getOption('port'))
			->setConsoleOutput($this->getOutput())
			->createServer()
			->run();

		return $this;
	}
}