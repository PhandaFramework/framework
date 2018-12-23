<?php

namespace Phanda\Filesystem;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class Filesystem extends SymfonyFilesystem
{
	/**
	 * Returns the time last modified
	 *
	 * @param $path
	 * @return bool|int
	 */
	public function lastModified($path)
	{
		return filemtime($path);
	}

	/**
	 * @param $path
	 * @return string
	 *
	 * @throws FileNotFoundException
	 */
	public function loadFile($path)
	{
		if (is_file($path)) {
			return file_get_contents($path);
		}

		throw new FileNotFoundException("File does not exists at {$path}");
	}

	/**
	 * @param $path
	 * @param $contents
	 * @return bool|int
	 */
	public function saveContents($path, $contents)
	{
		return file_put_contents($path, $contents, 0);
	}

	/**
	 * @param $path
	 * @return bool
	 */
	public function fileExists($path)
	{
		return is_file($path);
	}

	/**
	 * Determine if the given path is a directory.
	 *
	 * @param  string $directory
	 * @return bool
	 */
	public function directoryExists($directory)
	{
		return is_dir($directory);
	}

	/**
	 * Create a directory.
	 *
	 * @param  string $path
	 * @param  int    $mode
	 * @param  bool   $recursive
	 * @param  bool   $force
	 * @return bool
	 */
	public function createDirectory($path, $mode = 0755, $recursive = false, $force = false)
	{
		if ($force) {
			return @mkdir($path, $mode, $recursive);
		}

		return mkdir($path, $mode, $recursive);
	}
}