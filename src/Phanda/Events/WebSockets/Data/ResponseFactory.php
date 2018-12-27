<?php

namespace Phanda\Events\WebSockets\Data;

use Exception;
use Phanda\Contracts\Events\WebSockets\Data\PayloadFormatter;

class ResponseFactory
{
	/**
	 * @var PayloadFormatter
	 */
	protected $formatter;

	/**
	 * ResponseFactory constructor.
	 *
	 * @param PayloadFormatter $formatter
	 */
	public function __construct(PayloadFormatter $formatter)
	{
		$this->formatter = $formatter;
	}

	/**
	 * Creates a properly formatted event response
	 *
	 * @param string     $eventName
	 * @param string     $channelName
	 * @param array|null $data
	 * @return string
	 */
	public function makeChannelEventResponse(string $eventName, string $channelName, ?array $data = [])
	{
		$responseArray = ['event' => $eventName, 'channel' => $channelName, 'data' => $this->formatDataArray($data)];

		return $this->formatter->formatPayload($responseArray);
	}

	/**
	 * Creates an event response prefixed with the system prefix 'phanda'
	 *
	 * @param string     $eventName
	 * @param string     $channelName
	 * @param array|null $data
	 * @return string
	 */
	public function makeSystemChannelEventResponse(string $eventName, string $channelName, ?array $data = [])
	{
		return $this->makeChannelEventResponse('phanda:' . $eventName, $channelName, $data);
	}

	/**
	 * Creates an event response that's not directly attached to a channel
	 *
	 * @param string     $eventName
	 * @param array|null $data
	 * @return string
	 */
	public function makeEventResponse(string $eventName, ?array $data = [])
	{
		$responseArray = ['event' => $eventName, 'data' => $this->formatDataArray($data)];

		return $this->formatter->formatPayload($responseArray);
	}

	/**
	 * Creates an event response that is prefixed with the system prefix
	 * 'phanda', with no attached channel
	 *
	 * @param string     $eventName
	 * @param array|null $data
	 * @return string
	 */
	public function makeSystemEventResponse(string $eventName, ?array $data = [])
	{
		return $this->makeEventResponse('phanda:' . $eventName, $data);
	}

	/**
	 * Formats an array of data to be in the format that the WebSocket can
	 * accept
	 *
	 * @param array $data
	 * @return string
	 */
	protected function formatDataArray(array $data)
	{
		return $this->formatter->formatPayload($data);
	}

	/**
	 * Makes a response for a given exception
	 *
	 * @param Exception $e
	 * @return string
	 */
	public function makeExceptionResponse(Exception $e)
	{
		return $this->makeSystemEventResponse(
			'error',
			[
				'message' => $e->getMessage(),
				'code' => $e->getCode()
			]
		);
	}

}