<?php

namespace Phanda\Scene\Engine;

class FileEngine extends AbstractEngine
{

    /**
     * Get the evaluated contents of the scene.
     *
     * @param  string $path
     * @param  array $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        $scene = file_get_contents($path);
        $this->setLastRenderedScene($scene);

        return $scene;
    }
}