<?php

namespace Phanda\Contracts\Scene\Engine;

interface Engine
{
    /**
     * Get the evaluated contents of the scene.
     *
     * @param  string  $path
     * @param  array   $data
     * @return string
     */
    public function get($path, array $data = []);

    /**
     * Get the last scene that was rendered.
     *
     * @return string
     */
    public function getLastRenderedScene();
}