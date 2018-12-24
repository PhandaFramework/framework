<?php

namespace Phanda\Providers\Events;

use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;
use Phanda\Contracts\Events\WebSockets\Data\PayloadFormatter;
use Phanda\Events\WebSockets\Data\BasePayloadFormatter;
use Phanda\Providers\AbstractServiceProvider;
use Ratchet\ConnectionInterface;

class WebSocketServiceProvider extends AbstractServiceProvider
{

	public function register()
	{
		$this->registerPayloadFormatter();
		$this->registerRatchetToPhandaAliases();
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

}