<?php

namespace Phanda\Contracts\Events\WebSockets\Connection;

use GuzzleHttp\Psr7\Request;
use Phanda\Contracts\Events\WebSockets\Application\SocketApp;
use Phanda\Events\WebSockets\Manager;
use Ratchet\ConnectionInterface;

/**
 * Interface Connection
 *
 * @package Phanda\Contracts\Events\WebSockets\Connection
 *
 * @property Request $httpRequest
 */
interface Connection extends ConnectionInterface
{
	/**
	 * Gets the socket id of a given connection
	 *
	 * @return string
	 */
	public function getSocketId(): string;

	/**
	 * Sets the socket id of a given connection
	 *
	 * @param string $socketId
	 * @return Connection
	 */
	public function setSocketId(string $socketId): Connection;

	/**
	 * Gets the WebSocket manager instance attached to this connection
	 *
	 * @return SocketApp
	 */
	public function getApplication(): SocketApp;

	/**
	 * Sets the WebSocket manager instance attached to this connection
	 *
	 * @param SocketApp $app
	 * @return Connection
	 */
	public function setApplication(SocketApp $app): Connection;

}