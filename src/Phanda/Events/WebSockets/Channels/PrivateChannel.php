<?php

namespace Phanda\Events\WebSockets\Channels;

use Phanda\Contracts\Events\WebSockets\Connection\Connection as ConnectionContract;

class PrivateChannel extends BaseChannel
{

	public function subscribe(ConnectionContract $connection, $payload)
	{
		$this->verifyConnectionSignature($connection, $payload);
		parent::subscribe($connection, $payload);
	}

}