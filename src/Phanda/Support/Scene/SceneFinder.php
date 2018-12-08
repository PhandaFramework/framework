<?php

namespace Phanda\Support\Scene;

use InvalidArgumentException;
use Phanda\Contracts\Support\Scene\SceneFinder as SceneFinderContract;
use Phanda\Filesystem\Filesystem;

class SceneFinder implements SceneFinderContract
{

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $paths;

    /**
     * @var array
     */
    protected $scenes = [];

    /**
     * @var array
     */
    protected $hints = [];

    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * SceneFinder constructor.
     *
     * @param Filesystem $filesystem
     * @param array $paths
     */
    public function __construct(Filesystem $filesystem, array $paths)
    {
        $this->filesystem = $filesystem;
        $this->paths = $paths;
    }

    /**
     * @param string $name
     * @return string
     */
    public function find($name)
    {
        $name = trim($name);

        if(isset($this->scenes[$name])) {
            return $this->scenes[$name];
        }

        if($this->hasHintInformation($name)) {
            return $this->scenes[$name] = $this->findNamespacedScene($name);
        }

        return $this->scenes[$name] = $this->findInPaths($name, $this->paths);
    }

    /**
     * Get the path to a scene with a named path.
     *
     * @param  string  $name
     * @return string
     */
    protected function findNamespacedScene($name)
    {
        [$namespace, $scene] = $this->parseNamespaceSegments($name);
        return $this->findInPaths($scene, $this->hints[$namespace]);
    }

    /**
     * Get the segments of a scene with a named path.
     *
     * @param  string  $name
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function parseNamespaceSegments($name)
    {
        $segments = explode(static::HINT_PATH_DELIMITER, $name);

        if (count($segments) !== 2) {
            throw new InvalidArgumentException("Scene [{$name}] has an invalid name.");
        }

        if (! isset($this->hints[$segments[0]])) {
            throw new InvalidArgumentException("No hint path defined for [{$segments[0]}].");
        }

        return $segments;
    }

    /**
     * Find the given scene in the list of paths.
     *
     * @param  string  $name
     * @param  array   $paths
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function findInPaths($name, $paths)
    {
        foreach ((array) $paths as $path) {
            foreach ($this->getPossibleSceneFiles($name) as $file) {
                if ($this->filesystem->exists($scenePath = $path.'/'.$file)) {
                    return $scenePath;
                }
            }
        }

        throw new InvalidArgumentException("Scene [{$name}] not found.");
    }

    /**
     * Get an array of possible scene files.
     *
     * @param  string  $name
     * @return array
     */
    protected function getPossibleSceneFiles($name)
    {
        return array_map(function ($extension) use ($name) {
            return str_replace('.', '/', $name).'.'.$extension;
        }, $this->extensions);
    }

    /**
     * Add a location to the finder.
     *
     * @param  string  $location
     * @return $this
     */
    public function addLocation($location)
    {
        $this->paths[] = $location;
        return $this;
    }

    /**
     * Prepend a location to the finder.
     *
     * @param  string  $location
     * @return $this
     */
    public function prependLocation($location)
    {
        array_unshift($this->paths, $location);
        return $this;
    }

    /**
     * Add a namespace hint to the finder.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return $this
     */
    public function addNamespace($namespace, $hints)
    {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = array_merge($this->hints[$namespace], $hints);
        }

        $this->hints[$namespace] = $hints;
        return $this;
    }

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return $this
     */
    public function prependNamespace($namespace, $hints)
    {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = array_merge($hints, $this->hints[$namespace]);
        }

        $this->hints[$namespace] = $hints;
        return $this;
    }

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return $this
     */
    public function replaceNamespace($namespace, $hints)
    {
        $this->hints[$namespace] = (array) $hints;
        return $this;
    }

    /**
     * Register an extension with the scene finder.
     *
     * @param  string  $extension
     * @return $this
     */
    public function addExtension($extension)
    {
        if (($index = array_search($extension, $this->extensions)) !== false) {
            unset($this->extensions[$index]);
        }

        array_unshift($this->extensions, $extension);
        return $this;
    }

    /**
     * Returns whether or not the scene name has any hint information.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasHintInformation($name)
    {
        return strpos($name, static::HINT_PATH_DELIMITER) > 0;
    }

    /**
     * Flush the cache of located scenes.
     *
     * @return $this
     */
    public function clear()
    {
        $this->scenes = [];
        return $this;
    }

    /**
     * Get the filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Get the active scene paths.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Get the namespace to file path hints.
     *
     * @return array
     */
    public function getHints()
    {
        return $this->hints;
    }

    /**
     * Get registered extensions.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

}