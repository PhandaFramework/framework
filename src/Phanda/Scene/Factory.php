<?php

namespace Phanda\Scene;

use InvalidArgumentException;
use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Events\Dispatcher;
use Phanda\Contracts\Scene\Engine\Engine;
use Phanda\Contracts\Scene\Factory as FactoryContract;
use Phanda\Contracts\Scene\Scene;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Contracts\Support\Scene\SceneFinder;
use Phanda\Exceptions\Scene\UnrecognizedExtensionException;
use Phanda\Scene\Engine\EngineResolver;
use Phanda\Scene\Events\CreatingSceneEvent;
use Phanda\Support\PhandArr;
use Phanda\Support\PhandaStr;
use Phanda\Support\Scene\SceneName;
use Phanda\Util\Scene\LayoutFactoryTrait;

class Factory implements FactoryContract
{

    use LayoutFactoryTrait;

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
        try {
            $this->sceneFinder->find($scene);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the evaluated scene contents for the given path.
     *
     * @param  string $path
     * @param  Arrayable|array $data
     * @param  array $mergeData
     * @return Scene
     *
     * @throws UnrecognizedExtensionException
     */
    public function file($path, $data = [], $mergeData = [])
    {
        $data = array_merge($mergeData, $this->parseData($data));

        return modify($this->sceneInstance($path, $path, $data), function ($scene) {
            /** @var Scene $scene */
            $this->eventDispatcher->dispatch('creating-scene:' . $scene->getName(), new CreatingSceneEvent($scene));
        });
    }

    /**
     * Get the evaluated scene contents for the given scene.
     *
     * @param  string $scene
     * @param  Arrayable|array $data
     * @param  array $mergeData
     * @return Scene
     *
     * @throws UnrecognizedExtensionException
     */
    public function create($scene, $data = [], $mergeData = [])
    {
        $path = $this->sceneFinder->find(
            $scene = $this->normalizeSceneName($scene)
        );

        $data = array_merge($mergeData, $this->parseData($data));
        return modify($this->sceneInstance($scene, $path, $data), function ($scene) {
            /** @var Scene $scene */
            $this->eventDispatcher->dispatch('creating-scene:' . $scene->getName(), new CreatingSceneEvent($scene));
        });
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
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            $this->sharedData[$key] = $value;
        }

        return $value;
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
        $this->sceneFinder->addNamespace($namespace, $hints);
        return $this;
    }

    /**
     * Add a new namespace to the loader at the start.
     *
     * @param  string $namespace
     * @param  string|array $hints
     * @return FactoryContract
     */
    public function prependNamespace($namespace, $hints)
    {
        $this->sceneFinder->prependNamespace($namespace, $hints);
        return $this;
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
        $this->sceneFinder->replaceNamespace($namespace, $hints);
        return $this;
    }


    /**
     * @param array|Arrayable $data
     * @return array
     */
    protected function parseData($data)
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }

    /**
     * @param $view
     * @param $path
     * @param $data
     * @return Scene
     *
     * @throws UnrecognizedExtensionException
     */
    protected function sceneInstance($view, $path, $data)
    {
        return new \Phanda\Scene\Scene(
            $this,
            $this->getEngineFromPath($path),
            $view,
            $path,
            $data
        );
    }

    /**
     * @param $path
     * @return Engine
     *
     * @throws UnrecognizedExtensionException
     */
    protected function getEngineFromPath($path)
    {
        if (!$extension = $this->getExtension($path)) {
            throw new UnrecognizedExtensionException("Unrecognized extension in file: {$path}");
        }

        $engine = $this->extensions[$extension];

        return $this->engineResolver->resolveEngine($engine);
    }

    /**
     * Get the extension used by the view file.
     *
     * @param  string $path
     * @return string
     */
    protected function getExtension($path)
    {
        $extensions = array_keys($this->extensions);

        return PhandArr::first($extensions, function ($value) use ($path) {
            return PhandaStr::endsIn('.' . $value, $path);
        });
    }

    /**
     * Normalizes a scene's name.
     *
     * @param $name
     * @return string
     */
    protected function normalizeSceneName($name)
    {
        return SceneName::normalize($name);
    }

    /**
     * Increment the rendering counter.
     *
     * @return $this
     */
    public function incrementRender()
    {
        $this->renderCount++;
        return $this;
    }

    /**
     * Decrement the rendering counter.
     *
     * @return $this
     */
    public function decrementRender()
    {
        $this->renderCount--;
        return $this;
    }

    /**
     * Check if there are no active render operations.
     *
     * @return bool
     */
    public function hasRendered()
    {
        return $this->renderCount == 0;
    }

    /**
     * @param $location
     * @return $this
     */
    public function addLocation($location)
    {
        $this->sceneFinder->addLocation($location);
        return $this;
    }

    /**
     * Register a valid scene extension and its engine.
     *
     * @param  string    $extension
     * @param  string    $engine
     * @param  \Closure  $resolver
     * @return $this
     */
    public function addExtension($extension, $engine, $resolver = null)
    {
        $this->sceneFinder->addExtension($extension);

        if (isset($resolver)) {
            $this->engineResolver->registerEngine($engine, $resolver);
        }

        unset($this->extensions[$extension]);

        $this->extensions = array_merge([$extension => $engine], $this->extensions);
        return $this;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Clear all of the factory state like sections and stacks.
     *
     * @return Factory
     */
    public function clearState()
    {
        $this->renderCount = 0;
        $this->clearStages();
        return $this;
    }

    /**
     * Clear all of the section contents if done rendering.
     *
     * @return Factory
     */
    public function clearStateIfDoneRendering()
    {
        if ($this->hasRendered()) {
            $this->clearState();
        }
        return $this;
    }

    /**
     * Clear the cache of scenes located by the finder.
     *
     * @return Factory
     */
    public function clearFinderCache()
    {
        $this->sceneFinder->clear();
        return $this;
    }

    /**
     * @return Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param Dispatcher $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher(Dispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * Get the IoC container instance.
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  Container  $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSharedItem($key, $default = null)
    {
        return PhandArr::get($this->sharedData, $key, $default);
    }

    /**
     * @return array
     */
    public function getSharedData()
    {
        return $this->sharedData;
    }
}