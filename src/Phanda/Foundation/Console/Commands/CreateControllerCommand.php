<?php

namespace Phanda\Foundation\Console\Commands;

use Phanda\Console\AbstractGenerationCommand;

class CreateControllerCommand extends AbstractGenerationCommand
{

	protected $name = 'create:controller';

	protected $description = 'Create a new controller for your application';

	/**
	 * Gets the path to the template to generate
	 *
	 * @return string
	 */
	protected function getTemplateFile(): string
	{
		return __DIR__.'/templates/controller.template';
	}

	/**
	 * Get the base path, either app_path() or core_path()
	 *
	 * @return string
	 */
	protected function getBasePath(): string
	{
		return app_path();
	}

	/**
	 * Get the generator namespace, i.e core or app. Can be overridden if an argument
	 * is provided.
	 *
	 * @return string
	 */
	protected function getGeneratorNamespace(): string
	{
		return 'App';
	}

	/**
	 * Get the default namespace for the class.
	 *
	 * @param  string $rootNamespace
	 * @return string
	 */
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace . '\Controllers';
	}
}