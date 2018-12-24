<?php

namespace Phanda\Exceptions\Events\WebSockets;

class SocketApplicationException extends WebSocketException
{
	/**
	 * Makes a new exception based on a id not being unique
	 *
	 * @param int $appId
	 * @return SocketApplicationException
	 */
	public static function makeFromUniqueId(int $appId)
	{
		return new self("A Socket Application is already registered with the id '{$appId}'");
	}

	/**
	 * Makes a new exception based on a key not being unique
	 *
	 * @param string $appKey
	 * @return SocketApplicationException
	 */
	public static function makeFromUniqueKey(string $appKey)
	{
		return new self("A Socket Application is already registered with the key '{$appKey}'");
	}

}