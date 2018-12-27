<?php

namespace Phanda\Contracts\Events\WebSockets\Data;

interface PayloadFormatter
{
	/**
	 * Formats a payload to be sent to connected clients
	 *
	 * @param $payload
	 * @return string
	 */
	public function formatPayload($payload): string;

	public function formatDataResponse($data);
}