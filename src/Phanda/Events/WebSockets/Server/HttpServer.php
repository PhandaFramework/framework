<?php

namespace Phanda\Events\WebSockets\Server;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;

class HttpServer extends \Ratchet\Http\HttpServer
{
	/**
	 * HttpServer constructor.
	 *
	 * @param HttpServerInterface $component
	 * @param int                 $maxRequestSize
	 */
	public function __construct(HttpServerInterface $component, int $maxRequestSize = 4096)
	{
		parent::__construct($component);
		$this->_reqParser->maxSize = $maxRequestSize;
	}

	public function onOpen(ConnectionInterface $conn)
	{
		parent::onOpen($conn);
	}

	public function onClose(ConnectionInterface $conn)
	{
		parent::onClose($conn);
	}

	public function onMessage(ConnectionInterface $from, $msg)
	{
		return parent::onMessage($from, $msg);
	}

	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		parent::onError($conn, $e);
	}
}