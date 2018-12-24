<?php

namespace Phanda\Events\WebSockets\Messages;

use Phanda\Support\PhandaStr;
use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Phanda\Contracts\Events\WebSockets\Channels\Manager as ChannelManager;

class MessageFactory
{

	/**
	 * @param MessageInterface    $message
	 * @param ConnectionContract $connection
	 * @param ChannelManager      $channelManager
	 * @return ChannelMessage|ClientMessage
	 */
	public function createMessage(
		MessageInterface $message,
		ConnectionContract $connection,
		ChannelManager $channelManager
	)
	{
		$payload = json_decode($message->getPayload());

		return PhandaStr::startsIn('channel:', $payload->event)
			? new ChannelMessage($payload, $connection, $channelManager)
			: new ClientMessage($payload, $connection, $channelManager);
	}

}