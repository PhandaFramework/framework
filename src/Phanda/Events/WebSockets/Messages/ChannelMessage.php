<?php

namespace Phanda\Events\WebSockets\Messages;

use Phanda\Contracts\Events\WebSockets\Messages\Message;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Phanda\Contracts\Events\WebSockets\Channels\Manager as ChannelManager;

class ChannelMessage implements Message
{
	/**
	 * @var MessageInterface
	 */
	protected $message;

	/**
	 * @var ConnectionInterface
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
	 * @param ConnectionInterface $connection
	 * @param ChannelManager      $channelManager
	 */
	public function __construct(MessageInterface $message, ConnectionInterface $connection, ChannelManager $channelManager)
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