<?php

namespace Phanda\Scene\Events;

use Phanda\Contracts\Scene\Scene;
use Phanda\Events\Event;

class CreatingSceneEvent extends Event
{
    /**
     * @var Scene
     */
    protected $scene;

    /**
     * CreatingSceneEvent constructor.
     * @param Scene $scene
     */
    public function __construct(Scene $scene)
    {
        $this->scene = $scene;
    }

    /**
     * @return Scene
     */
    public function getScene(): Scene
    {
        return $this->scene;
    }


}