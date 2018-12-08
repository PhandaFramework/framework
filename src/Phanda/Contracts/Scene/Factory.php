<?php

namespace Phanda\Contracts\Scene;

use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Support\Arrayable;

interface Factory
{
    /**
     * Determine if a given scene exists.
     *
     * @param  string  $scene
     * @return bool
     */
    public function exists($scene);

    /**
     * Get the evaluated scene contents for the given path.
     *
     * @param  string  $path
     * @param  Arrayable|array  $data
     * @param  array  $mergeData
     * @return Scene
     */
    public function file($path, $data = [], $mergeData = []);

    /**
     * Get the evaluated scene contents for the given scene.
     *
     * @param  string  $scene
     * @param  Arrayable|array  $data
     * @param  array  $mergeData
     * @return Scene
     */
    public function make($scene, $data = [], $mergeData = []);

    /**
     * Add a piece of shared data to the environment.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function share($key, $value = null);

    /**
     * Add a new namespace to the loader.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return $this
     */
    public function addNamespace($namespace, $hints);

    /**
     * Add a new namespace to the loader at the start.
     *
     * @param  string $namespace
     * @param  string|array $hints
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
     * Check if there are no active render operations.
     *
     * @return bool
     */
    public function hasRendered();

    /**
     * @param $location
     * @return $this
     */
    public function addLocation($location);

    /**
     * Register a valid scene extension and its engine.
     *
     * @param  string    $extension
     * @param  string    $engine
     * @param  \Closure  $resolver
     * @return $this
     */
    public function addExtension($extension, $engine, $resolver = null);

    /**
     * @return array
     */
    public function getExtensions();

    /**
     * Clear all of the factory state like sections and stacks.
     *
     * @return $this
     */
    public function clearState();

    /**
     * Clear all of the section contents if done rendering.
     *
     * @return $this
     */
    public function clearStateIfDoneRendering();

    /**
     * Clear the cache of scenes located by the finder.
     *
     * @return $this
     */
    public function clearFinderCache();

    /**
     * Get the IoC container instance.
     *
     * @return Container
     */
    public function getContainer();

    /**
     * Set the IoC container instance.
     *
     * @param  Container  $container
     * @return $this
     */
    public function setContainer(Container $container);

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSharedItem($key, $default = null);

    /**
     * @return array
     */
    public function getSharedData();
}