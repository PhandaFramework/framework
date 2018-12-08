<?php

namespace Phanda\Scene;

use Phanda\Contracts\Scene\Engine\Engine;
use Phanda\Contracts\Scene\Scene as SceneContract;
use Phanda\Contracts\Support\Arrayable;

class Scene implements SceneContract
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var string
     */
    private $view;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $data;

    /**
     * Scene constructor.
     *
     * @param Factory $factory
     * @param Engine $engine
     * @param string $view
     * @param string $path
     * @param mixed $data
     */
    public function __construct(Factory $factory, Engine $engine, $view, $path, $data)
    {
        $this->factory = $factory;
        $this->engine = $engine;
        $this->view = $view;
        $this->path = $path;
        $this->data = $data instanceof Arrayable ? $data->toArray() : (array)$data;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        // TODO: Implement getName() method.
    }
}