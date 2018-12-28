<?php

namespace Phanda\Events\WebSockets\Channels;

use Phanda\Contracts\Events\WebSockets\Channels\Channel;
use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;
use Phanda\Contracts\Events\WebSockets\Data\PayloadFormatter;
use Phanda\Events\WebSockets\Data\ResponseFactory;
use Phanda\Exceptions\Events\WebSockets\InvalidSocketSignature;
use Phanda\Support\PhandArr;
use Phanda\Support\PhandaStr;

class BaseChannel implements Channel
{
	/**
	 * The name of the channel
	 *
	 * @var string
	 */
	protected $channelName;

	/**
	 * The payload formatter
	 *
	 * @var PayloadFormatter
	 */
	protected $formatter;

	/**
	 * The response factory for generating responses
	 *
	 * @var ResponseFactory
	 */
	protected $responseFactory;

	/**
	 * The subscribed connections to this channel
	 *
	 * @var ConnectionContract[]
	 */
	protected $subscribers = [];

	public function __construct(string $channelName)
	{
		$this->channelName = $channelName;
		$this->formatter = phanda()->create(PayloadFormatter::class);
		$this->responseFactory = phanda()->create(ResponseFactory::class);
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return [
			'connection_count' => count($this->getSubscribedConnections()),
			'is_occupied' => $this->hasConnections()
		];
	}

	/**
	 * Gets all the currently subscribed connections in this channel
	 *
	 * @return array
	 */
	public function getSubscribedConnections(): array
	{
		return $this->subscribers;
	}

	/**
	 * Checks if there are any currently active connections on this channel
	 *
	 * @return bool
	 */
	public function hasConnections(): bool
	{
		return count($this->subscribers) > 0;
	}

	/**
	 * Subscribes to this channel
	 *
	 * @param ConnectionContract  $connection
	 * @param                     $payload
	 * @return void
	 */
	public function subscribe(ConnectionContract $connection, $payload)
	{
		$this->saveSubscriber($connection);

		$connection->send(
			$this->responseFactory->makeSystemChannelEventResponse(
				'subscribed',
				$this->getChannelName(),
				[
					'channel' => $this->getChannelName()
				]
			)
		);
	}

	/**
	 * Verifies that a given socket signature is valid for the given
	 * connection
	 *
	 * @param ConnectionContract $connection
	 * @param mixed              $payload
	 *
	 * @throws InvalidSocketSignature
	 */
	protected function verifyConnectionSignature(ConnectionContract $connection, $payload)
	{
		$signature = "{$connection->getSocketId()}:{$this->getChannelName()}";

		/*if (isset($payload->data->channel_data)) {
			$signature .= ":{$payload->channel_data}";
		}*/

		if (hash_hmac('sha256', $payload->data->auth, $connection->getApplication()->getAppSecret()) !== hash_hmac('sha256', $signature, $connection->getApplication()->getAppSecret())) {
			throw new InvalidSocketSignature("Socket mismatch in connection to channel.");
		}
	}

	/**
	 * Saves the connection to the internal array
	 *
	 * Can be overridden in children classes that wish to provide
	 * additional functionality to saving a subscriber.
	 *
	 * @param ConnectionContract $connection
	 */
	protected function saveSubscriber(ConnectionContract $connection)
	{
		$this->subscribers[$connection->getSocketId()] = $connection;
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
	 * Unsubscribes from this channel
	 *
	 * @param ConnectionContract $connection
	 * @return void
	 */
	public function unsubscribe(ConnectionContract $connection)
	{
		unset($this->subscribers[$connection->getSocketId()]);

		$connection->send(
			$this->responseFactory->makeSystemChannelEventResponse(
				'unsubscribed',
				$this->getChannelName(),
				[
					'channel' => $this->getChannelName()
				]
			)
		);
	}

	/**
	 * Broadcasts a message to everyone in this channel, except the sender
	 *
	 * @param ConnectionContract  $connection
	 * @param                     $payload
	 * @return void
	 */
	public function broadcastToOthers(ConnectionContract $connection, $payload)
	{
		$this->broadcastToAllExcept($payload, $connection->getSocketId());
	}

	/**
	 * Broadcasts to everyone in the channel except for the people with the associated socket id's
	 *
	 * @param               $payload
	 * @param string[]|null $socketIds
	 * @return void
	 */
	public function broadcastToAllExcept($payload, $socketIds = null)
	{
		if (is_null($socketIds)) {
			$this->broadcast($payload);
			return;
		}

		$payload = $this->formatPayload($payload);
		$socketIds = PhandArr::makeArray($socketIds);

		foreach ($this->subscribers as $subscriber) {
			if (in_array($subscriber->getSocketId(), $socketIds)) {
				continue;
			}

			$subscriber->send($payload);
		}
	}

	/**
	 * Broadcasts an event to everyone in this channel
	 *
	 * @param $payload
	 * @return void
	 */
	public function broadcast($payload)
	{
		$payload = $this->formatPayload($payload);

		foreach ($this->subscribers as $subscriber) {
			$subscriber->send($payload);
		}
	}

	/**
	 * Helper function to format payload
	 *
	 * @param $payload
	 * @return string
	 */
	protected function formatPayload($payload): string
	{
		return $this->formatter->formatPayload($payload);
	}
}