<?php

namespace Phanda\Events\WebSockets\Messages;

use Phanda\Contracts\Events\WebSockets\Messages\Message;
use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;
use Phanda\Contracts\Events\WebSockets\Channels\Manager as ChannelManager;
use Phanda\Events\WebSockets\Data\ResponseFactory;
use Phanda\Support\PhandaStr;

class ChannelMessage implements Message
{
	/**
	 * @var \stdClass
	 */
	protected $payload;

	/**
	 * @var ConnectionContract
	 */
	protected $connection;

	/**
	 * @var ChannelManager
	 */
	protected $channelManager;

	/**
	 * @var ResponseFactory
	 */
	protected $responseFactory;

	/**
	 * ClientMessage constructor.
	 *
	 * @param \stdClass          $payload
	 * @param ConnectionContract $connection
	 * @param ChannelManager     $channelManager
	 */
	public function __construct(\stdClass $payload, ConnectionContract $connection, ChannelManager $channelManager)
	{
		$this->payload = $payload;
		$this->connection = $connection;
		$this->channelManager = $channelManager;
		$this->responseFactory = phanda()->create(ResponseFactory::class);
	}

	/**
	 * Responds to a message
	 *
	 * @return void
	 */
	public function respond()
	{
		$eventName = PhandaStr::makeCamel(PhandaStr::after($this->payload->event, ':'));

		if (method_exists($this, $eventName)) {
			call_user_func([$this, $eventName], $this->connection, $this->payload->data ?? new \stdClass());
		}
	}

	/**
	 * Responds to a ping message
	 *
	 * @param ConnectionContract $connection
	 */
	protected function ping(ConnectionContract $connection)
	{
		$connection->send(
			$this->responseFactory->makeSystemEventResponse('pong')
		);
	}

	/**
	 * Subscribes to a channel, or creates one if it does not exist
	 *
	 * @param ConnectionContract $connection
	 * @param \stdClass          $payload
	 */
	protected function subscribe(ConnectionContract $connection, \stdClass $payload)
	{
		$channel = $this->channelManager->findOrCreate($connection->getApplication()->getAppId(), $payload->channel);
		$channel->subscribe($connection, $payload);
	}

	/**
	 * Unsubscribes from a channel
	 *
	 * @param ConnectionContract $connection
	 * @param \stdClass          $payload
	 */
	public function unsubscribe(ConnectionContract $connection, \stdClass $payload)
	{
		$channel = $this->channelManager->findOrCreate($connection->getApplication()->getAppId(), $payload->channel);
		$channel->unsubscribe($connection);
	}
}