<?php

namespace Phanda\Events\WebSockets\Messages;

use Phanda\Contracts\Events\WebSockets\Messages\Message;
use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;
use Phanda\Contracts\Events\WebSockets\Channels\Manager as ChannelManager;
use Phanda\Support\PhandaStr;

class ClientMessage implements Message
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
	}

	/**
	 * Responds to a message
	 *
	 * @return void
	 */
	public function respond()
	{
		if (!PhandaStr::startsIn('client-', $this->payload->event)) {
			return;
		}

		$channel = $this->channelManager->find($this->connection->getApplication()->getAppId(), $this->payload->channel);
		$channel->broadcastToOthers($this->connection, $this->payload);
	}
}