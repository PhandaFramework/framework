<?php

namespace Phanda\Contracts\Scene;

use Phanda\Contracts\Scene\Engine\Engine;

interface Scene
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Add a piece of data to the scene.
     *
     * @param  string|array $key
     * @param  mixed $value
     * @return $this
     */
    public function attach($key, $value = null);

    /**
     * Get the string contents of the scene.
     *
     * @param  callable|null  $callback
     * @return string
     *
     * @throws \Throwable
     */
    public function render(callable $callback = null);

    /**
     * Add a scene instance to the scene data.
     *
     * @param  string  $key
     * @param  string  $view
     * @param  array   $data
     * @return $this
     */
    public function attachSelf($key, $view, array $data = []);

    /**
     * Get the array of scene data.
     *
     * @return array
     */
    public function getData();

    /**
     * Get the path to the view file.
     *
     * @return string
     */
    public function getPath();

    /**
     * Set the path to the view.
     *
     * @param  string  $path
     * @return $this
     */
    public function setPath($path);

    /**
     * Get the view factory instance.
     *
     * @return Factory
     */
    public function getFactory();

    /**
     * Get the view's rendering engine.
     *
     * @return Engine
     */
    public function getEngine();
}