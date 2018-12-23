<?php

namespace Phanda\Foundation\Console\Commands;

use Phanda\Console\AbstractGenerationCommand;
use Phanda\Support\PhandaInflector;

class CreateEntityCommand extends AbstractGenerationCommand
{

	protected $name = 'create:entity';

	protected $description = 'Create a new entity for your application';

	/**
	 * Gets the path to the template to generate
	 *
	 * @return string
	 */
	protected function getTemplateFile(): string
	{
		return __DIR__.'/templates/entity.template';
	}

	/**
	 * Get the default namespace for the class, this gets appended to baseNamespace
	 *
	 * @param  string $rootNamespace
	 * @return string
	 */
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace . '\Model\Entity';
	}

	/**
	 * Get the base path, either app_path() or core_path()
	 *
	 * @return string
	 */
	protected function getBasePath(): string
	{
		return core_path();
	}

	/**
	 * Get the generator namespace, i.e 'Core' or 'App'. Can be overridden if an argument
	 * is provided.
	 *
	 * @return string
	 */
	protected function getGeneratorNamespace(): string
	{
		return 'Core';
	}

	protected function getNameInput(): string
	{
		$baseName = parent::getNameInput();

		return PhandaInflector::classify($baseName);
	}
}