<?php

namespace Phanda\Contracts\Support\Scene;

use Phanda\Filesystem\Filesystem;

interface SceneFinder
{
    /**
     * Hint path delimiter value.
     *
     * @var string
     */
    const HINT_PATH_DELIMITER = '::';

    /**
     * @param string $name
     * @return string
     */
    public function find($name);

    /**
     * Add a location to the finder.
     *
     * @param  string  $location
     * @return $this
     */
    public function addLocation($location);

    /**
     * Prepend a location to the finder.
     *
     * @param  string  $location
     * @return $this
     */
    public function prependLocation($location);

    /**
     * Add a namespace hint to the finder.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return $this
     */
    public function addNamespace($namespace, $hints);

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return $this
     */
    public function prependNamespace($namespace, $hints);

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return $this
     */
    public function replaceNamespace($namespace, $hints);

    /**
     * Register an extension with the scene finder.
     *
     * @param  string  $extension
     * @return $this
     */
    public function addExtension($extension);

    /**
     * Returns whether or not the scene name has any hint information.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasHintInformation($name);

    /**
     * Flush the cache of located scenes.
     *
     * @return $this
     */
    public function clear();

    /**
     * Get the filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem();

    /**
     * Get the active scene paths.
     *
     * @return array
     */
    public function getPaths();

    /**
     * Get the namespace to file path hints.
     *
     * @return array
     */
    public function getHints();

    /**
     * Get registered extensions.
     *
     * @return array
     */
    public function getExtensions();
}