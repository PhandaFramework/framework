<?php

namespace Phanda\Contracts\Scene;

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
     * Replace the namespace hints for the given namespace.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return $this
     */
    public function replaceNamespace($namespace, $hints);
}