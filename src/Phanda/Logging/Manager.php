<?php

namespace Phanda\Logging;

class Manager
{

	/**
	 * @var array
	 */
	protected $loggers = [];

	/**
	 * Manager constructor.
	 *
	 * @param array $loggers
	 */
	public function __construct($loggers = [])
	{
		$this->loggers = $loggers;
	}

	/**
	 * Gets an individual logger that has been registered with the manager.
	 *
	 * @param $name
	 * @return Logger|null
	 */
	public function getLogger($name = "default"): ?Logger
	{
		return $this->loggers[$name] ?? null;
	}

	/**
	 * Registers a logger with the manager.
	 *
	 * @param string $name
	 * @param Logger $logger
	 * @return Manager
	 */
	public function registerLogger(string $name, Logger $logger): Manager
	{
		$this->loggers[$name] = $logger;

		return $this;
	}

	/**
	 * Sets the internal loggers array.
	 *
	 * @param array $loggers
	 * @return Manager
	 */
	public function setLoggers(array $loggers): Manager
	{
		$this->loggers = $loggers;
		return $this;
	}

	/**
	 * Gets the internal loggers array.
	 *
	 * @return array
	 */
	public function getLoggers(): array
	{
		return $this->loggers;
	}

}