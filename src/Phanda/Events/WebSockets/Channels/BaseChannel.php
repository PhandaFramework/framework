<?php

namespace Phanda\Events\WebSockets\Channels;

use Phanda\Contracts\Events\WebSockets\Channels\Channel;
use Ratchet\ConnectionInterface;

class BaseChannel implements Channel
{
	protected $channelName;

	public function __construct(string $channelName)
	{
		$this->channelName = $channelName;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		// TODO: Implement toArray() method.
	}

	/**
	 * Checks if there are any currently active connections on this channel
	 *
	 * @return bool
	 */
	public function hasConnections(): bool
	{
		// TODO: Implement hasConnections() method.
	}

	/**
	 * Gets all the currently subscribed connections in this channel
	 *
	 * @return array
	 */
	public function getSubscribedConnections(): array
	{
		// TODO: Implement getSubscribedConnections() method.
	}

	/**
	 * Subscribes to this channel
	 *
	 * @param ConnectionInterface $connection
	 * @param                     $payload
	 * @return mixed
	 */
	public function subscribe(ConnectionInterface $connection, $payload)
	{
		// TODO: Implement subscribe() method.
	}

	/**
	 * Unsubscribes from this channel
	 *
	 * @param ConnectionInterface $connection
	 * @return mixed
	 */
	public function unsubscribe(ConnectionInterface $connection)
	{
		// TODO: Implement unsubscribe() method.
	}

	/**
	 * Broadcasts an event to everyone in this channel
	 *
	 * @param $payload
	 * @return mixed
	 */
	public function broadcast($payload)
	{
		// TODO: Implement broadcast() method.
	}

	/**
	 * Broadcasts a message to everyone in this channel, except the sender
	 *
	 * @param ConnectionInterface $connection
	 * @param                     $payload
	 * @return mixed
	 */
	public function broadcastToOthers(ConnectionInterface $connection, $payload)
	{
		// TODO: Implement broadcastToOthers() method.
	}

	/**
	 * Broadcasts to everyone in the channel except for the people with the associated socket id's
	 *
	 * @param               $payload
	 * @param string[]|null $socketId
	 * @return mixed
	 */
	public function broadcastToAllExcept($payload, ?array $socketId = null)
	{
		// TODO: Implement broadcastToAllExcept() method.
	}

	/**
	 * Sets the channel name
	 *
	 * @param string $channelName
	 * @return $this
	 */
	public function setChannelName(string $channelName)
	{
		$this->channelName = $channelName;
		return $this;
	}

	/**
	 * Gets the channel name
	 *
	 * @return string
	 */
	public function getChannelName(): string
	{
		return $this->channelName;
	}
}