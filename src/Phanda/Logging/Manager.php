<?php

namespace Phanda\Logging;

use Monolog\Handler\StreamHandler;
use Phanda\Contracts\Events\Dispatcher;
use Phanda\Support\PhandArr;

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
	 * Makes a logger from configuration, and then sets the logger internally
	 *
	 * @param string $name
	 * @param array  $configuration
	 * @return Manager
	 *
	 * @throws \Exception
	 */
	public function makeLogger(string $name, array $configuration): Manager
	{
		$internalLogger = new \Monolog\Logger(PhandArr::get($configuration, 'channel', 'default'));

		foreach(PhandArr::makeArray($configuration['drivers']) as $driver) {
			switch(strtolower(trim($driver))) {
				case 'file':
				default:
					$driver = new StreamHandler(
						PhandArr::get($configuration, 'file_info', storage_path('logs/debug.log')),
						PhandArr::get($configuration, 'log_level', 'debug')
					);
					break;
			}

			$internalLogger->pushHandler($driver);
		}

		$dispatcher = app()->create(Dispatcher::class);
		$logger = new Logger($internalLogger, $dispatcher);
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