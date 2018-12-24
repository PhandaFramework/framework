<?php

namespace Phanda\Events\WebSockets\Connection;

use Phanda\Contracts\Events\WebSockets\Application\SocketApp;
use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;
use Phanda\Events\WebSockets\Manager;

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
	 *
	 * @todo this
	 */
	function send($data)
	{
		return $this;
	}

	/**
	 * Close the connection
	 *
	 * @todo this
	 */
	function close()
	{
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
}