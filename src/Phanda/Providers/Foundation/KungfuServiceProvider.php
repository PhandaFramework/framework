<?php

namespace Phanda\Providers\Foundation;

use Phanda\Foundation\Console\Commands\ApplicationDebugCommand;
use Phanda\Foundation\Console\Commands\CreateControllerCommand;
use Phanda\Foundation\Console\Commands\CreateEntityCommand;
use Phanda\Foundation\Console\Commands\CreateTableCommand;
use Phanda\Foundation\Console\Commands\EnvironmentCommand;
use Phanda\Foundation\Console\Commands\ServeCommand;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Console\Application as Kungfu;
use Phanda\Support\PhandArr;

class KungfuServiceProvider extends AbstractServiceProvider
{

	protected $defer = true;

	protected $commands = [

	];

	protected $devCommands = [
		'command.debug' => ApplicationDebugCommand::class,
		'command.environment' => EnvironmentCommand::class,
		'command.serve' => ServeCommand::class,
		'command.create.controller' => CreateControllerCommand::class,
		'command.create.entity' => CreateEntityCommand::class,
		'command.create.table' => CreateTableCommand::class
	];

	public function register()
	{
		$this->registerCommands(
			array_merge($this->commands, $this->devCommands)
		);

		$this->registerUserRegisteredCommands();
	}

	/**
	 * @param array $commands
	 */
	protected function registerCommands(array $commands)
	{
		foreach ($commands as $key => $command) {
			$this->phanda->singleton($key, function () use ($command) {
				return $this->phanda->create($command);
			});
		}

		$this->commands(array_values($commands));
	}

	/**
	 * Registers the commands with Kungfu
	 *
	 * @param $commands
	 */
	public function commands($commands)
	{
		$commands = is_array($commands) ? $commands : func_get_args();

		Kungfu::starting(function ($kungfu) use ($commands) {
			/** @var Kungfu $kungfu */
			$kungfu->resolveCommands($commands);
		});
	}

	/**
	 * Registers the commands that have been added in the command configuration
	 * files in the application.
	 */
	public function registerUserRegisteredCommands()
	{
		$commandDirectories = config('commands.folders', []);
		$commands = config('commands.commands', []);

		$commands = PhandArr::makeArray($commands);
		$registered = [];

		foreach ($commands as $key => $userCommand) {
			$registered['application.command.' . $key] = $userCommand;

			$this->phanda->singleton('application.command.' . $key, function () use ($userCommand) {
				return $this->phanda->create($userCommand);
			});
		}

		Kungfu::starting(function ($kungfu) use ($registered, $commandDirectories) {
			/** @var Kungfu $kungfu */
			$kungfu->loadCommandsInDir($commandDirectories);
			$kungfu->resolveCommands($registered);
		});
	}

}