<?php

namespace Phanda\Events\WebSockets\Data;

use Phanda\Contracts\Events\WebSockets\Data\PayloadFormatter;

class BasePayloadFormatter implements PayloadFormatter
{

	/**
	 * Formats a payload to be sent to connected clients
	 *
	 * @param $payload
	 * @return string
	 */
	public function formatPayload($payload): string
	{
		return json_encode($payload);
	}

	/**
	 * Formats the data
	 *
	 * @param $data
	 * @return array
	 */
	public function formatDataResponse($data)
	{
		return createDictionary($data)->toArray();
	}
}