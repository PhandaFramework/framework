<?php

namespace Phanda\Events\WebSockets\Channels\Managers;

use Phanda\Contracts\Events\WebSockets\Channels\Channel;
use Phanda\Contracts\Events\WebSockets\Channels\Manager;
use Phanda\Events\WebSockets\Channels\BaseChannel;
use Phanda\Events\WebSockets\Channels\PrivateChannel;
use Phanda\Events\WebSockets\Channels\UserAwareChannel;
use Phanda\Support\PhandArr;
use Phanda\Support\PhandaStr;
use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;

class ArrayChannelManager implements Manager
{
	/** @var array|Channel[] */
	protected $channels = [];

	/**
	 * Finds a channel matching the given application id, and channel id
	 * or creates one.
	 *
	 * @param string $appId
	 * @param string $channelId
	 * @return Channel
	 */
	public function findOrCreate(string $appId, string $channelId): Channel
	{
		if ($channel = $this->find($appId, $channelId)) {
			return $channel;
		}

		$channelClass = $this->getChannelClass($channelId);
		return $this->channels[$appId][$channelId] = new $channelClass($channelId);
	}

	/**
	 * Finds a channel matching the given application id, and channel id
	 * or returns null
	 *
	 * @param string $appId
	 * @param string $channelId
	 * @return Channel|null
	 */
	public function find(string $appId, string $channelId): ?Channel
	{
		return $this->channels[$appId][$channelId] ?? null;
	}

	/**
	 * Gets all the currently active channels for an application
	 *
	 * @param string $appId
	 * @return array
	 */
	public function getApplicationChannels(string $appId): array
	{
		return $this->channels[$appId] ?? [];
	}

	/**
	 * Gets the count of the currently active connections in the given
	 * channel
	 *
	 * @param string $appId
	 * @return int
	 */
	public function getApplicationConnectionCount(string $appId): int
	{
		return createDictionary($this->getApplicationChannels($appId))->sumOf(
			function ($channel) {
				/** @var Channel $channel */
				return count($channel->getSubscribedConnections());
			}
		);
	}

	/**
	 * Removes a given connection from all channels
	 *
	 * @param ConnectionContract $connection
	 * @return void
	 */
	public function removeConnection(ConnectionContract $connection)
	{
		if (!isset($connection->app)) {
			return;
		}

		createDictionary(PhandArr::get($this->channels, $connection->app->id, []))->each(function ($channel) use ($connection) {
			/** @var Channel $channel */
			$channel->unsubscribe($connection);
		});

		$this->cleanChannels($connection->app->id);
	}

	/**
	 * Determines the channel class from the channel name
	 *
	 * @param string $channelName
	 * @return string
	 */
	protected function getChannelClass(string $channelName): string
	{
		if (PhandaStr::startsIn('private-', $channelName)) {
			return PrivateChannel::class;
		}

		if (PhandaStr::startsIn('user-aware-', $channelName)) {
			return UserAwareChannel::class;
		}

		return BaseChannel::class;
	}

	/**
	 * Removes all channels that don't currently have anyone connected.
	 *
	 * @param string $appId
	 */
	protected function cleanChannels(string $appId)
	{
		foreach ($this->channels[$appId] as $channelName => $channel) {
			/** @var Channel $channel */
			if ($channel->hasConnections()) {
				continue;
			}

			unset($this->channels[$appId][$channelName]);
		}

		if(count($this->channels[$appId]) === 0) {
			unset($this->channels[$appId]);
		}
	}
}