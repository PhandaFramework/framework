<?php

namespace Phanda\Events\WebSockets\Messages;

use Phanda\Support\PhandaStr;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Phanda\Contracts\Events\WebSockets\Channels\Manager as ChannelManager;

class MessageFactory
{

	/**
	 * @param MessageInterface    $message
	 * @param ConnectionInterface $connection
	 * @param ChannelManager      $channelManager
	 * @return ChannelMessage|ClientMessage
	 */
	public function createMessage(
		MessageInterface $message,
		ConnectionInterface $connection,
		ChannelManager $channelManager
	)
	{
		$payload = json_decode($message->getPayload());

		return PhandaStr::startsIn('channel:', $payload->event)
			? new ChannelMessage($payload, $connection, $channelManager)
			: new ClientMessage($payload, $connection, $channelManager);
	}

}