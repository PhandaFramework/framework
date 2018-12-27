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
		dump('onopen');
		parent::onOpen($conn);
	}

	public function onClose(ConnectionInterface $conn)
	{
		dump('onclose');
		parent::onClose($conn);
	}

	public function onMessage(ConnectionInterface $from, $msg)
	{
		dump('onmessage');
		return parent::onMessage($from, $msg);
	}

	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		dump('onerror');
		dump($e);
		parent::onError($conn, $e);
	}
}