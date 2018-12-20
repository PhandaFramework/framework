<?php

namespace Phanda\Logging\Events;

use Phanda\Events\Event;

class LogTriggeredEvent extends Event
{
	/**
	 * @var mixed
	 */
	protected $level;

	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var array
	 */
	protected $context;

	/**
	 * LogTriggeredEvent constructor.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 */
	public function __construct($level, string $message, array $context)
	{
		$this->level = $level;
		$this->message = $message;
		$this->context = $context;
	}

	/**
	 * @param mixed $level
	 * @return LogTriggeredEvent
	 */
	public function setLevel($level)
	{
		$this->level = $level;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLevel()
	{
		return $this->level;
	}

	/**
	 * @param string $message
	 * @return LogTriggeredEvent
	 */
	public function setMessage(string $message): LogTriggeredEvent
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->message;
	}

	/**
	 * @param array $context
	 * @return LogTriggeredEvent
	 */
	public function setContext(array $context): LogTriggeredEvent
	{
		$this->context = $context;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getContext(): array
	{
		return $this->context;
	}

}