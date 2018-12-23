<?php

namespace Phanda\Console;

use Phanda\Filesystem\Filesystem;
use Phanda\Support\PhandaStr;
use Symfony\Component\Console\Input\InputArgument;

abstract class AbstractGenerationCommand extends ConsoleCommand
{
	/**
	 * @var Filesystem
	 */
	protected $filesystem;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * AbstractGenerationCommand constructor.
	 *
	 * @param Filesystem $filesystem
	 */
	public function __construct(Filesystem $filesystem)
	{
		parent::__construct();

		$this->filesystem = $filesystem;
	}

	/**
	 * Gets the path to the stub to generate
	 *
	 * @return string
	 */
	abstract protected function getStubFile(): string;

	/**
	 * Handle the generation of the stub
	 *
	 * @return bool
	 */
	public function handle()
	{
		$name = $this->qualifyClass($this->getNameInput());

		$path = $this->getPath($name);

		if ((! $this->hasOption('force') ||
				! $this->getOption('force')) &&
			$this->alreadyExists($this->getNameInput())) {
			$this->error($this->type.' already exists!');

			return false;
		}

		$this->makeDirectory($path);

		$this->filesystem->saveContents($path, $this->buildClass($name));

		$this->info($this->type.' created successfully.');
	}

	/**
	 * Parse the class name and format according to the root namespace.
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function qualifyClass($name)
	{
		$name = ltrim($name, '\\/');

		$rootNamespace = $this->getBaseNamespace();

		if (PhandaStr::startsIn($rootNamespace, $name)) {
			return $name;
		}

		$name = str_replace('/', '\\', $name);

		return $this->qualifyClass(
			$this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name
		);
	}

	/**
	 * Get the default namespace for the class.
	 *
	 * @param  string $rootNamespace
	 * @return string
	 */
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace;
	}

	/**
	 * Determine if the class already exists.
	 *
	 * @param  string $rawName
	 * @return bool
	 */
	protected function alreadyExists($rawName)
	{
		return $this->filesystem->fileExists($this->getPath($this->qualifyClass($rawName)));
	}

	/**
	 * Get the destination class path.
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function getPath($name)
	{
		$name = PhandaStr::replaceFirst($this->getBaseNamespace(), '', $name);

		return $this->getBasePath() . '/' . str_replace('\\', '/', $name) . '.php';
	}

	protected abstract function getBasePath(): string;

	/**
	 * Build the directory for the class if necessary.
	 *
	 * @param  string $path
	 * @return string
	 */
	protected function makeDirectory($path)
	{
		if (!$this->filesystem->directoryExists(dirname($path))) {
			$this->filesystem->createDirectory(dirname($path), 0777, true, true);
		}

		return $path;
	}

	/**
	 * Build the class with the given name.
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function buildClass($name)
	{
		$stub = $this->filesystem->loadFile($this->getStubFile());

		return $this->replaceNamespace($stub, $name)->replaceClassName($stub, $name);
	}

	/**
	 * Replace the namespace for the given stub.
	 *
	 * @param  string $stub
	 * @param  string $name
	 * @return $this
	 */
	protected function replaceNamespace(&$stub, $name)
	{
		$stub = str_replace(
			['%namespace%', '%base_namespace%'],
			[$this->getNamespace($name), $this->getBaseNamespace()],
			$stub
		);

		return $this;
	}

	/**
	 * Get the full namespace for a given class, without the class name.
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function getNamespace($name)
	{
		return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
	}

	/**
	 * Replace the class name for the given stub.
	 *
	 * @param  string $stub
	 * @param  string $name
	 * @return string
	 */
	protected function replaceClassName($stub, $name)
	{
		$class = str_replace($this->getNamespace($name) . '\\', '', $name);

		return str_replace('%class_name%', $class, $stub);
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
	 * Gets the base namespace, or applies the override
	 *
	 * @return string
	 */
	protected function getBaseNamespace(): string
	{
		if ($this->hasOption('namespace')) {
			return $this->getOption('namespace');
		}

		return $this->getGeneratorNamespace();
	}

	/**
	 * Get the generator namespace, i.e core or app. Can be overridden if an argument
	 * is provided.
	 *
	 * @return string
	 */
	protected abstract function getGeneratorNamespace(): string;

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