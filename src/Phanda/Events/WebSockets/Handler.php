<?php

namespace Phanda\Events\WebSockets;

use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class Handler implements MessageComponentInterface
{

	/**
	 * When a new connection is opened it will be passed to this method
	 *
	 * @param  ConnectionContract $conn The socket/connection that just connected to your application
	 * @throws \Exception
	 */
	function onOpen(ConnectionContract $conn)
	{
	}

	/**
	 * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not
	 * result in an error if it has already been closed.
	 *
	 * @param  ConnectionContract $conn The socket/connection that is closing/closed
	 * @throws \Exception
	 */
	function onClose(ConnectionContract $conn)
	{
	}

	/**
	 * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
	 * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through
	 * this method
	 *
	 * @param  ConnectionContract $conn
	 * @param  \Exception          $e
	 * @throws \Exception
	 */
	function onError(ConnectionContract $conn, \Exception $e)
	{
	}

	public function onMessage(ConnectionContract $conn, MessageInterface $msg)
	{
	}
}