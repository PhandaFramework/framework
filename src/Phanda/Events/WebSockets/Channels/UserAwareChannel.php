<?php

namespace Phanda\Events\WebSockets\Channels;

use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;

class UserAwareChannel extends BaseChannel
{
	/**
	 * An array of the currently connected users in this
	 * channel
	 *
	 * @var array
	 */
	protected $users = [];

	/**
	 * Gets the list of currently connected users to this channel
	 *
	 * @return array
	 */
	public function getConnectedUsers(): array
	{
		return $this->users;
	}

	/**
	 * Overrides the subscribe function to handle the addition of users
	 *
	 * @param ConnectionContract $connection
	 * @param mixed              $payload
	 */
	public function subscribe(ConnectionContract $connection, $payload)
	{
		$this->verifyConnectionSignature($connection, $payload);
		$this->saveSubscriber($connection);

		$channelData = json_decode($payload->channel_data);
		$this->users[$connection->getSocketId()] = $channelData;

		$connection->send(
			$this->responseFactory->makeSystemChannelEventResponse(
				'subscribed',
				$this->getChannelName(),
				$this->getChannelData()
			)
		);

		$this->broadcastToOthers(
			$connection,
			$this->responseFactory->makeSystemChannelEventResponse(
				'member_connected',
				$this->getChannelName(),
				$channelData
			)
		);
	}

	/**
	 * Overrides the unsubscribe function to handle the removal of
	 * users that are connected in this channel
	 *
	 * @param ConnectionContract $connection
	 */
	public function unsubscribe(ConnectionContract $connection)
	{
		parent::unsubscribe($connection);

		if (!isset($this->users[$connection->getSocketId()])) {
			return;
		}

		$this->broadcastToOthers(
			$connection,
			$this->responseFactory->makeSystemChannelEventResponse(
				'member_disconnected',
				$this->getChannelName(),
				[
					'user_id' => $this->users[$connection->getSocketId()]->user_id
				]
			)
		);

		unset($this->users[$connection->getSocketId()]);
	}

	/**
	 * Gets the data of this channel
	 *
	 * @return array
	 */
	protected function getChannelData(): array
	{
		return [
			'presence' => [
				'ids' => $this->getUserIds(),
				'hash' => $this->getHash(),
				'count' => count($this->users),
			],
		];
	}

	/**
	 * Converts this channel to an array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'user_count' => count($this->users),
		]);
	}

	/**
	 * Gets the list of the current user ids in the current channel
	 *
	 * @return array
	 */
	protected function getUserIds(): array
	{
		$userIds = array_map(function ($channelData) {
			return (string)$channelData->user_id;
		}, $this->users);

		return array_values($userIds);
	}

	/**
	 * Gets the hash of the current channel
	 *
	 * @return array
	 */
	protected function getHash(): array
	{
		$hash = [];

		foreach ($this->users as $socketId => $channelData) {
			$hash[$channelData->user_id] = $channelData->user_info;
		}

		return $hash;
	}
}