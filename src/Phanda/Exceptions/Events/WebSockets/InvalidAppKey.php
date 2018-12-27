<?php

namespace Phanda\Exceptions\Events\WebSockets;

class InvalidAppKey extends WebSocketException
{

	public function __construct($appKey)
	{
		parent::__construct("The WebSocket app key '{$appKey}' has not been registered or is invalid.", 0, null);
	}

}