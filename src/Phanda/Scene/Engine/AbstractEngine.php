<?php

namespace Phanda\Scene\Engine;

use Phanda\Contracts\Scene\Engine\Engine as EngineContract;

abstract class AbstractEngine implements EngineContract
{
    /**
     * The scene that was last to be rendered.
     *
     * @var string
     */
    protected $lastRendered;

    /**
     * Get the last scene that was rendered.
     *
     * @return string
     */
    public function getLastRenderedScene()
    {
        return $this->lastRendered;
    }

    /**
     * @param string $renderedScene
     */
    protected function setLastRenderedScene($renderedScene)
    {
        $this->lastRendered = $renderedScene;
    }
}