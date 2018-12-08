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
    public function getLastRendered()
    {
        return $this->lastRendered;
    }
}