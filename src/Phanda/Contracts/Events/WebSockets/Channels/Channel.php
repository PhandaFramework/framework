<?php

namespace Phanda\Contracts\Events\WebSockets\Channels;

use Phanda\Contracts\Support\Arrayable;
use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;

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
	 * @param ConnectionContract $connection
	 * @param                     $payload
	 * @return void
	 */
	public function subscribe(ConnectionContract $connection, $payload);

	/**
	 * Unsubscribes from this channel
	 *
	 * @param ConnectionContract $connection
	 * @return void
	 */
	public function unsubscribe(ConnectionContract $connection);

	/**
	 * Broadcasts an event to everyone in this channel
	 *
	 * @param $payload
	 * @return void
	 */
	public function broadcast($payload);

	/**
	 * Broadcasts a message to everyone in this channel, except the sender
	 *
	 * @param ConnectionContract $connection
	 * @param                     $payload
	 * @return void
	 */
	public function broadcastToOthers(ConnectionContract $connection, $payload);

	/**
	 * Broadcasts to everyone in the channel except for the people with the associated socket id's
	 *
	 * @param               $payload
	 * @param string[]|string|null $socketIds
	 * @return void
	 */
	public function broadcastToAllExcept($payload, $socketIds = null);

	/**
	 * Gets the channel name
	 *
	 * @return string
	 */
	public function getChannelName(): string;

	/**
	 * Sets the channel name
	 *
	 * @param string $channelName
	 * @return $this
	 */
	public function setChannelName(string $channelName);

}