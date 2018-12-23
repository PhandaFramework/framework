<?php

namespace Phanda\Contracts\Events\WebSockets\Channels;

use Phanda\Contracts\Support\Arrayable;
use Ratchet\ConnectionInterface;

interface Channel extends Arrayable
{
	/**
	 * Checks if there are any currently active connections on this channel
	 *
	 * @return bool
	 */
	public function hasConnections(): bool;

	/**
	 * Gets all the currently subscribed connections in this channel
	 *
	 * @return array
	 */
	public function getSubscribedConnections(): array;

	/**
	 * Subscribes to this channel
	 *
	 * @param ConnectionInterface $connection
	 * @param                     $payload
	 * @return mixed
	 */
	public function subscribe(ConnectionInterface $connection, $payload);

	/**
	 * Unsubscribes from this channel
	 *
	 * @param ConnectionInterface $connection
	 * @return mixed
	 */
	public function unsubscribe(ConnectionInterface $connection);

	/**
	 * Broadcasts an event to everyone in this channel
	 *
	 * @param $payload
	 * @return mixed
	 */
	public function broadcast($payload);

	/**
	 * Broadcasts a message to everyone in this channel, except the sender
	 *
	 * @param ConnectionInterface $connection
	 * @param                     $payload
	 * @return mixed
	 */
	public function broadcastToOthers(ConnectionInterface $connection, $payload);

	/**
	 * Broadcasts to everyone in the channel except for the people with the associated socket id's
	 *
	 * @param               $payload
	 * @param string[]|null $socketId
	 * @return mixed
	 */
	public function broadcastToAllExcept($payload, ?array $socketId = null);

}