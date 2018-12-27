<?php

namespace Phanda\Events\WebSockets;

use Phanda\Contracts\Events\WebSockets\Channels\Manager as ManagerContract;
use Phanda\Contracts\Events\WebSockets\Connection\Connection as PhandaConnection;
use Phanda\Events\WebSockets\Connection\Connection;
use Phanda\Events\WebSockets\Data\ResponseFactory;
use Phanda\Events\WebSockets\Messages\MessageFactory;
use Phanda\Exceptions\Events\WebSockets\InvalidAppKeyException;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class Handler implements MessageComponentInterface
{
	/**
	 * @var ManagerContract
	 */
	protected $channelManager;

	/**
	 * @var Manager
	 */
	protected $manager;

	/**
	 * @var ResponseFactory
	 */
	protected $responseFactory;

	/**
	 * @var MessageFactory
	 */
	protected $messageFactory;

	/**
	 * Handler constructor.
	 *
	 * @param ManagerContract $channelManager
	 * @param Manager         $manager
	 */
	public function __construct(ManagerContract $channelManager, Manager $manager)
	{
		$this->channelManager = $channelManager;
		$this->manager = $manager;
		$this->responseFactory = phanda()->create(ResponseFactory::class);
		$this->messageFactory = phanda()->create(MessageFactory::class);
	}

	/**
	 * When a new connection is opened it will be passed to this method
	 *
	 * @param  ConnectionInterface $conn The socket/connection that just connected to your application
	 * @throws \Exception
	 */
	function onOpen(ConnectionInterface $conn)
	{
		$connection = $this->makePhandaConnection($conn);

		$this->verifyApplicationKey($connection)
			->generateSocketId($connection)
			->establishConnection($connection);
	}

	/**
	 * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not
	 * result in an error if it has already been closed.
	 *
	 * @param  ConnectionInterface $conn The socket/connection that is closing/closed
	 * @throws \Exception
	 */
	function onClose(ConnectionInterface $conn)
	{
		$conn = $this->convertConnection($conn);
		$this->channelManager->removeConnection($conn);
	}

	/**
	 * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
	 * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through
	 * this method
	 *
	 * @param  ConnectionInterface $conn
	 * @param  \Exception          $e
	 * @throws \Exception
	 */
	function onError(ConnectionInterface $conn, \Exception $e)
	{
		$conn = $this->convertConnection($conn);
		$conn->send(
			$this->responseFactory->makeExceptionResponse($e)
		);
	}

	/**
	 * Sends a message
	 *
	 * @param ConnectionInterface $conn
	 * @param MessageInterface    $msg
	 */
	public function onMessage(ConnectionInterface $conn, MessageInterface $msg)
	{
		$conn = $this->convertConnection($conn);
		$message = $this->messageFactory->createMessage($msg, $conn, $this->channelManager);
		$message->respond();
	}

	/**
	 * Creates a new phanda connection from the current connection
	 *
	 * @param ConnectionInterface $connection
	 * @return PhandaConnection
	 */
	protected function makePhandaConnection(ConnectionInterface $connection): PhandaConnection
	{
		return new Connection($connection);
	}

	/**
	 * Converts a connection to be a PhandaConnection if it is not already one
	 *
	 * @param ConnectionInterface $connection
	 * @return PhandaConnection
	 */
	protected function convertConnection(ConnectionInterface $connection): PhandaConnection
	{
		if (!$connection instanceof PhandaConnection) {
			return $this->makePhandaConnection($connection);
		}

		return $connection;
	}

	/**
	 * Verifies the application key in the request exists
	 *
	 * @param PhandaConnection $connection
	 * @return $this
	 */
	protected function verifyApplicationKey(PhandaConnection $connection)
	{
		$parameters = [];
		parse_str($connection->httpRequest->getUri()->getQuery(), $parameters);
		$appKey = $parameters['appKey'] ?? '';

		if (!$app = $this->manager->getApplicationByKey($appKey)) {
			throw new InvalidAppKeyException($appKey);
		}

		$connection->setApplication($app);
		return $this;
	}

	/**
	 * Generates a socket id for the given connection
	 *
	 * @param PhandaConnection $connection
	 * @return $this
	 * @throws \Exception
	 */
	protected function generateSocketId(PhandaConnection $connection)
	{
		$socketId = sprintf('%d.%d', random_int(1, 1000000000), random_int(1, 1000000000));
		$connection->setSocketId($socketId);
		return $this;
	}

	/**
	 * Establishes the connection
	 *
	 * @param PhandaConnection $connection
	 * @return $this
	 */
	protected function establishConnection(PhandaConnection $connection)
	{
		$connection->send(
			$this->responseFactory->makeSystemEventResponse(
				'connection_established',
				[
					'socket_id' => $connection->getSocketId(),
					'timeout' => 30
				]
			)
		);
		return $this;
	}
}