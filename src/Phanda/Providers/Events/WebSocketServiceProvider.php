<?php

namespace Phanda\Providers\Events;

use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;
use Phanda\Contracts\Events\WebSockets\Data\PayloadFormatter;
use Phanda\Contracts\Foundation\Application;
use Phanda\Events\WebSockets\Channels\Managers\ArrayChannelManager;
use Phanda\Events\WebSockets\Commands\StartWebSocketServer;
use Phanda\Events\WebSockets\Data\BasePayloadFormatter;
use Phanda\Events\WebSockets\Handler;
use Phanda\Events\WebSockets\Manager;
use Phanda\Providers\AbstractServiceProvider;
use Ratchet\ConnectionInterface;
use Phanda\Console\Application as Kungfu;

class WebSocketServiceProvider extends AbstractServiceProvider
{

	public function register()
	{
		$this->registerPayloadFormatter();
		$this->registerRatchetToPhandaAliases();
		$this->registerWebSocketManager();
		$this->registerWebSocketChannelManager();
		$this->registerWebSocketHandler();
		$this->registerWebSocketCommands();
	}

	/**
	 * Registers the base payload formatter and its aliases
	 */
	protected function registerPayloadFormatter()
	{
		$this->phanda->singleton(PayloadFormatter::class, function () {
			return new BasePayloadFormatter();
		});

		$this->phanda->alias(PayloadFormatter::class, BasePayloadFormatter::class);
	}

	/**
	 * Registers the aliases from ratchet to phanda
	 */
	protected function registerRatchetToPhandaAliases()
	{
		$this->phanda->alias(ConnectionInterface::class, ConnectionContract::class);
	}

	/**
	 * Registers the web socket manager to allow the booting of applications
	 */
	protected function registerWebSocketManager()
	{
		$this->phanda->singleton(Manager::class, function () {
			$configuration = config('websockets.apps');

			return new Manager($configuration);
		});
	}

	/**
	 * Registers the web socket channel manager and its respective aliases
	 */
	protected function registerWebSocketChannelManager()
	{
		$this->phanda->singleton(\Phanda\Contracts\Events\WebSockets\Channels\Manager::class, function () {
			return new ArrayChannelManager();
		});

		$this->phanda->alias(\Phanda\Contracts\Events\WebSockets\Channels\Manager::class, ArrayChannelManager::class);
	}

	/**
	 * Registers the web socket handler
	 */
	protected function registerWebSocketHandler()
	{
		$this->phanda->singleton(Handler::class, function($phanda) {
			/** @var Application $phanda */
			/** @var \Phanda\Contracts\Events\WebSockets\Channels\Manager $channelManager */
			$channelManager = $phanda->create(\Phanda\Contracts\Events\WebSockets\Channels\Manager::class);
			/** @var Manager $manager */
			$manager = $phanda->create(Manager::class);

			return new Handler($channelManager, $manager);
		});
	}

	/**
	 *
	 */
	protected function registerWebSocketCommands()
	{
		$this->phanda->singleton('commands.websockets.serve', function() {
			return new StartWebSocketServer();
		});

		Kungfu::starting(function ($kungfu) {
			/** @var Kungfu $kungfu */
			$kungfu->resolveCommands(['commands.websockets.serve' => StartWebSocketServer::class]);
		});
	}

}