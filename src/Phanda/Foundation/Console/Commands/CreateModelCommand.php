<?php

namespace Phanda\Foundation\Console\Commands;

use Phanda\Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;

class CreateModelCommand extends ConsoleCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'create:model';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Creates a model for your application. Both the table and entity classes are generated.';

	/**
	 * @throws \Exception
	 */
	public function handle()
	{
		$name = $this->getNameInput();
		$this->info('Making entity...');
		$this->makeEntity($name);
		$this->info('Making table...');
		$this->makeTable($name);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	protected function makeEntity($name)
	{
		$this->call('create:entity', ['name' => $name]);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	protected function makeTable($name)
	{
		$this->call('create:table', ['name' => $name]);
	}

	/**
	 * Get the desired class name from the input.
	 *
	 * @return string
	 */
	protected function getNameInput(): string
	{
		return trim($this->getInputArgument('name'));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	public function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'The name of the class']
		];
	}
}