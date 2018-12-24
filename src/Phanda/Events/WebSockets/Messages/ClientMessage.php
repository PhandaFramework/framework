<?php

namespace Phanda\Events\WebSockets\Messages;

use Phanda\Contracts\Events\WebSockets\Messages\Message;
use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Phanda\Contracts\Events\WebSockets\Channels\Manager as ChannelManager;

class ClientMessage implements Message
{
	/**
	 * @var MessageInterface
	 */
	protected $message;

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
	 * @param MessageInterface    $message
	 * @param ConnectionContract $connection
	 * @param ChannelManager      $channelManager
	 */
	public function __construct(MessageInterface $message, ConnectionContract $connection, ChannelManager $channelManager)
	{
		$this->message = $message;
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
		// TODO: Implement respond() method.
	}
}