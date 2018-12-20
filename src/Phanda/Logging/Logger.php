<?php

namespace Phanda\Logging;

use Phanda\Events\Dispatcher;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Logger constructor.
	 *
	 * @param LoggerInterface $logger
	 * @param Dispatcher      $dispatcher
	 */
	public function __construct(LoggerInterface $logger, Dispatcher $dispatcher)
	{
		$this->logger = $logger;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function emergency($message, array $context = [])
	{

	}

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function alert($message, array $context = [])
	{

	}

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function critical($message, array $context = [])
	{
		// TODO: Implement critical() method.
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function error($message, array $context = [])
	{

	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function warning($message, array $context = [])
	{

	}

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function notice($message, array $context = [])
	{

	}

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function info($message, array $context = [])
	{

	}

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function debug($message, array $context = [])
	{

	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function log($level, $message, array $context = [])
	{

	}
}