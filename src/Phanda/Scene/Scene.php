<?php

namespace Phanda\Scene;

use BadMethodCallException;
use Phanda\Contracts\Scene\Engine\Engine;
use Phanda\Contracts\Scene\Factory;
use Phanda\Contracts\Scene\Scene as SceneContract;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Contracts\Support\Renderable;
use Phanda\Scene\Engine\SceneCompilerEngine;
use Phanda\Scene\Events\RenderingSceneEvent;
use Phanda\Support\PhandaStr;

class Scene implements SceneContract
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var SceneCompilerEngine
     */
    private $engine;

    /**
     * @var string
     */
    private $scene;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $data;

	/**
	 * @var bool
	 */
    protected $startedRender = false;

    /**
     * Scene constructor.
     *
     * @param Factory $factory
     * @param Engine $engine
     * @param string $scene
     * @param string $path
     * @param mixed $data
     */
    public function __construct(Factory $factory, Engine $engine, $scene, $path, $data)
    {
        $this->factory = $factory;
        $this->engine = $engine;
        $this->engine->setScene($this);
        $this->scene = $scene;
        $this->path = $path;
        $this->data = $data instanceof Arrayable ? $data->toArray() : (array)$data;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->scene;
    }

    /**
     * Add a piece of data to the scene.
     *
     * @param  string|array $key
     * @param  mixed $value
     * @return SceneContract
     */
    public function attach($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the string contents of the scene.
     *
     * @param  callable|null $callback
     * @return string
     *
     * @throws \Throwable
     */
    public function render(callable $callback = null)
    {
        try {
        	$this->startedRender = true;
            $contents = $this->renderContents();
            $response = isset($callback) ? $callback($this, $contents) : null;
            $this->factory->clearStateIfDoneRendering();
            return is_null($response) ? $contents : $response;
        } catch (\Exception $e) {
            $this->factory->clearState();
            throw $e;
        } catch (\Throwable $e) {
            $this->factory->clearState();
            throw $e;
        }
    }

    /**
     * @return string
     */
    protected function renderContents()
    {
        $this->factory->incrementRender();

        $this->factory->getEventDispatcher()
            ->dispatch(
                'rendering-scene:' . $this->getName(),
                new RenderingSceneEvent($this)
            );

        $contents = $this->getSceneContents();

        $this->factory->decrementRender();

        return $contents;
    }

    /**
     * @return string
     */
    protected function getSceneContents()
    {
        return $this->engine->get($this->path, $this->gatherData());
    }

    /**
     * @return array
     */
    protected function gatherData()
    {
        $data = array_merge($this->factory->getSharedData(), $this->data);

        foreach ($data as $key => $value) {
            if ($value instanceof Renderable) {
                $data[$key] = $value->render();
            }
        }

        return $data;
    }

    /**
     * Add a scene instance to the scene data.
     *
     * @param  string $key
     * @param  string $scene
     * @param  array $data
     * @return SceneContract
     */
    public function attachSelf($key, $scene, array $data = [])
    {
        return $this->attach($key, $this->factory->create($scene, $data));
    }

    /**
     * Get the array of scene data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the path to the scene file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path to the scene.
     *
     * @param  string $path
     * @return SceneContract
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get the scene factory instance.
     *
     * @return Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Get the scene's rendering engine.
     *
     * @return Engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Get a piece of data from the scene.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the scene.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->attach($key, $value);
    }

    /**
     * Check if a piece of data is bound to the scene.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a piece of bound data from the scene.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Dynamically bind parameters to the scene.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return \Phanda\Contracts\Scene\Scene
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (!PhandaStr::startsIn( 'with', $method)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        return $this->attach(PhandaStr::makeCamel(substr($method, 4)), $parameters[0]);
    }

    /**
     * Get the string contents of the scene.
     *
     * @return string
     *
     * @throws \Throwable
     */
    public function __toString()
    {
        return $this->render();
    }
}