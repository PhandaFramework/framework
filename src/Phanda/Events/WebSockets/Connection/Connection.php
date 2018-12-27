<?php

namespace Phanda\Events\WebSockets\Connection;

use Phanda\Contracts\Events\WebSockets\Application\SocketApp;
use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;
use Phanda\Events\WebSockets\Manager;
use Ratchet\ConnectionInterface;

class Connection implements ConnectionContract
{

	/**
	 * The socket id of this connection
	 *
	 * @var string
	 */
	protected $socketId;

	/**
	 * The websocket manager of this connection
	 *
	 * @var SocketApp
	 */
	protected $app;

	/**
	 * The internal connection
	 *
	 * @var ConnectionInterface
	 */
	protected $internalConnection;

	public function __construct(ConnectionInterface $connection)
	{
		$this->internalConnection = $connection;
	}

	/**
	 * Gets the socket id of a given connection
	 *
	 * @return string
	 */
	public function getSocketId(): string
	{
		return $this->socketId;
	}

	/**
	 * Sets the socket id of a given connection
	 *
	 * @param string $socketId
	 * @return ConnectionContract
	 */
	public function setSocketId(string $socketId): ConnectionContract
	{
		$this->socketId = $socketId;
		return $this;
	}

	/**
	 * Send data to the connection
	 *
	 * @param  string $data
	 * @return Connection
	 */
	function send($data)
	{
		$this->internalConnection->send($data);
		return $this;
	}

	/**
	 * Close the connection
	 */
	function close()
	{
		$this->internalConnection->close();
	}

	/**
	 * Gets the WebSocket manager instance attached to this connection
	 *
	 * @return SocketApp
	 */
	public function getApplication(): SocketApp
	{
		return $this->app;
	}

	/**
	 * Sets the WebSocket manager instance attached to this connection
	 *
	 * @param SocketApp $app
	 * @return ConnectionContract
	 */
	public function setApplication(SocketApp $app): ConnectionContract
	{
		$this->app = $app;
		return $this;
	}

	public function __call($name, $arguments)
	{
		return $this->internalConnection->{$name}($arguments);
	}

	public function __get($name)
	{
		return $this->internalConnection->{$name};
	}

	public function __set($name, $value)
	{
		$this->internalConnection->{$name} = $value;
	}
}