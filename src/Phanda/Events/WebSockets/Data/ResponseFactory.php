<?php

namespace Phanda\Events\WebSockets\Data;

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
		$responseArray = ['event' => $eventName, 'channel' => $channelName] + $data;

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
		return $this->makeChannelEventResponse('phanda:'.$eventName, $channelName, $data);
	}

}