<?php

namespace Phanda\Contracts\Events\WebSockets\Connection;

use Ratchet\ConnectionInterface;

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

}