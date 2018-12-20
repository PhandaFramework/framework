<?php

namespace Phanda\Logging;

use Phanda\Events\Dispatcher;
use Phanda\Logging\Events\LogTriggeredEvent;
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
		$this->handleLog(__FUNCTION__, $message, $context);
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
		$this->handleLog(__FUNCTION__, $message, $context);
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
		$this->handleLog(__FUNCTION__, $message, $context);
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
		$this->handleLog(__FUNCTION__, $message, $context);
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
		$this->handleLog(__FUNCTION__, $message, $context);
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
		$this->handleLog(__FUNCTION__, $message, $context);
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
		$this->handleLog(__FUNCTION__, $message, $context);
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
		$this->handleLog(__FUNCTION__, $message, $context);
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
		$this->handleLog($level, $message, $context);
	}

	/**
	 * Sets the internal logger of this logger.
	 *
	 * This will be the actual handler of the logger.
	 *
	 * @param LoggerInterface $logger
	 * @return Logger
	 */
	public function setLogger(LoggerInterface $logger): Logger
	{
		$this->logger = $logger;
		return $this;
	}

	/**
	 * Gets the internal logger
	 *
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface
	{
		return $this->logger;
	}

	/**
	 * Sets the event dispatcher of this logger
	 *
	 * @param Dispatcher $dispatcher
	 * @return Logger
	 */
	public function setDispatcher(Dispatcher $dispatcher): Logger
	{
		$this->dispatcher = $dispatcher;
		return $this;
	}

	/**
	 * Gets the event dispatcher of this logger
	 *
	 * @return Dispatcher
	 */
	public function getDispatcher(): Dispatcher
	{
		return $this->dispatcher;
	}

	/**
	 * Handles the firing of the log, and sending to the internal logger.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 */
	protected function handleLog($level, $message, $context)
	{
		$this->fireLogEvent($level, $message, $context);
		$this->logger{$level}($message, $context);
	}

	/**
	 * Handles the firing of the log
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 */
	protected function fireLogEvent($level, $message, $context)
	{
		$this->dispatcher->dispatch('log.fired', new LogTriggeredEvent($level, $message, $context));
	}
}