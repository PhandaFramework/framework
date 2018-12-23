<?php

namespace Phanda\Contracts\Events\WebSockets\Channels;

use Ratchet\ConnectionInterface;

interface Manager
{

	/**
	 * Finds a channel matching the given application id, and channel id
	 * or creates one.
	 *
	 * @param string $appId
	 * @param string $channelId
	 * @return Channel
	 */
	public function findOrCreate(string $appId, string $channelId): Channel;

	/**
	 * Finds a channel matching the given application id, and channel id
	 * or returns null
	 *
	 * @param string $appId
	 * @param string $channelId
	 * @return Channel|null
	 */
	public function find(string $appId, string $channelId): ?Channel;

	/**
	 * Gets all the currently active channels for an application
	 *
	 * @param string $appId
	 * @return array
	 */
	public function getApplicationChannels(string $appId): array;

	/**
	 * Gets the count of the currently active connections in the given
	 * channel
	 *
	 * @param string $appId
	 * @return int
	 */
	public function getApplicationConnectionCount(string $appId): int;

	/**
	 * Removes a given connection from all channels
	 *
	 * @param ConnectionInterface $connection
	 * @return void
	 */
	public function removeConnection(ConnectionInterface $connection);

}