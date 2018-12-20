<?php

namespace Phanda\Providers\Logging;

use Phanda\Foundation\Application;
use Phanda\Logging\Manager;
use Phanda\Providers\AbstractServiceProvider;

class LoggingServiceProvider extends AbstractServiceProvider
{

	/**
	 * Handles the registering of the loggers that are defined in the application configuration.
	 *
	 * Also handles the creation of the LoggingManager - which handles the individual logs.
	 */
	public function register()
	{
		$this->registerManager();
	}

	/**
	 * Registers the Logger Manager.
	 */
	protected function registerManager()
	{
		$this->phanda->singleton(Manager::class, function ($phanda) {
			/** @var Application $phanda */
			$manager = new Manager();
			$this->registerLoggersFromConfiguration($manager);
			return $manager;
		});
	}

	/**
	 * Loads the configuration for the loggers and proceeds to create the respective loggers
	 *
	 * @param Manager $manager
	 */
	protected function registerLoggersFromConfiguration(Manager $manager)
	{
	}

}