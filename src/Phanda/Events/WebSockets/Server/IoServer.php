<?php

namespace Phanda\Events\WebSockets\Server;

use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ServerInterface;

class IoServer extends \Ratchet\Server\IoServer
{
	/**
	 * IoServer constructor.
	 *
	 * @param MessageComponentInterface $app
	 * @param ServerInterface           $socket
	 * @param LoopInterface|null        $loop
	 */
	public function __construct(MessageComponentInterface $app, ServerInterface $socket, ?LoopInterface $loop = null)
	{
		parent::__construct($app, $socket, $loop);
	}
}