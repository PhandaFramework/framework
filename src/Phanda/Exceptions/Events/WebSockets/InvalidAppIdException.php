<?php

namespace Phanda\Exceptions\Events\WebSockets;

class InvalidAppIdException extends WebSocketException
{

	public function __construct($appId)
	{
		parent::__construct("The WebSocket app id '{$appId}' has not been registered or is invalid.", 0, null);
	}

}