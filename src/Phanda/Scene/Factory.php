<?php

namespace Phanda\Scene;

use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Events\Dispatcher;
use Phanda\Contracts\Scene\Factory as FactoryContract;
use Phanda\Contracts\Scene\Scene;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Contracts\Support\Scene\SceneFinder;
use Phanda\Scene\Engine\EngineResolver;

class Factory implements FactoryContract
{

    /**
     * @var EngineResolver
     */
    protected $engineResolver;

    /**
     * @var SceneFinder
     */
    protected $sceneFinder;

    /**
     * @var Dispatcher
     */
    protected $eventDispatcher;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $sharedData = [];

    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * @var int
     */
    protected $renderCount = 0;

    /**
     * The Scene Factory constructor.
     *
     * @param EngineResolver $engineResolver
     * @param SceneFinder $sceneFinder
     * @param Dispatcher $eventDispatcher
     */
    public function __construct(EngineResolver $engineResolver, SceneFinder $sceneFinder, Dispatcher $eventDispatcher)
    {
        $this->engineResolver = $engineResolver;
        $this->sceneFinder = $sceneFinder;
        $this->eventDispatcher = $eventDispatcher;

        $this->share('__scene', $this);
    }

    /**
     * Determine if a given scene exists.
     *
     * @param  string $scene
     * @return bool
     */
    public function exists($scene)
    {
        // TODO: Implement exists() method.
    }

    /**
     * Get the evaluated scene contents for the given path.
     *
     * @param  string $path
     * @param  Arrayable|array $data
     * @param  array $mergeData
     * @return Scene
     */
    public function file($path, $data = [], $mergeData = [])
    {
        // TODO: Implement file() method.
    }

    /**
     * Get the evaluated scene contents for the given scene.
     *
     * @param  string $scene
     * @param  Arrayable|array $data
     * @param  array $mergeData
     * @return Scene
     */
    public function make($scene, $data = [], $mergeData = [])
    {
        // TODO: Implement make() method.
    }

    /**
     * Add a piece of shared data to the environment.
     *
     * @param  array|string $key
     * @param  mixed $value
     * @return mixed
     */
    public function share($key, $value = null)
    {
        // TODO: Implement share() method.
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string $namespace
     * @param  string|array $hints
     * @return FactoryContract
     */
    public function addNamespace($namespace, $hints)
    {
        // TODO: Implement addNamespace() method.
    }

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @param  string $namespace
     * @param  string|array $hints
     * @return FactoryContract
     */
    public function replaceNamespace($namespace, $hints)
    {
        // TODO: Implement replaceNamespace() method.
    }
}