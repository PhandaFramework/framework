<?php

namespace Phanda\Support\Facades\Scene;

use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Events\Dispatcher;
use Phanda\Contracts\Scene\Factory;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Exceptions\ExceptionHandler;
use Phanda\Foundation\Http\Request;
use Phanda\Support\Facades\Facade;
use Phanda\Contracts\Scene\Scene as BaseScene;

/**
 * Class Scene
 * The base Scene facade.
 *
 * @package Phanda\Support\Facades\Scene
 * @see Factory
 *
 * @method static bool exists(string $scene)
 * @method static BaseScene file(string $path, Arrayable|array $data = [], array $mergeData = [])
 * @method static BaseScene create(string $scene, Arrayable|array $data = [], array $mergeData = [])
 * @method static mixed share(array|string $key, mixed $value)
 * @method static Factory addNamespace(string $namespace, string|array $hints)
 * @method static Factory prependNamespace(string $namespace, string|array $hints)
 * @method static Factory replaceNamespace(string $namespace, string|array $hints)
 * @method static bool hasRendered()
 * @method static Factory incrementRender()
 * @method static Factory decrementRender()
 * @method static Factory addLocation(mixed $location)
 * @method static Factory addExtension(string $extension, string $engine, \Closure $resolver)
 * @method static array getExtensions()
 * @method static Factory clearState()
 * @method static Factory clearStateIfDoneRendering()
 * @method static Factory clearFinderCache()
 * @method static Dispatcher getEventDispatcher()
 * @method static Factory setEventDispatcher(Dispatcher $eventDispatcher)
 * @method static Container getContainer()
 * @method static Factory setContainer(Container $container)
 * @method static mixed getSharedItem(string $key, mixed $default)
 * @method static array getSharedData()
 */
class Scene extends Facade
{

    /**
     * Set up the name of the Facade instance being resolved. Used internally for checking if the Facade has been
     * resolved or not.
     *
     * @return string
     */
    protected static function getFacadeName(): string
    {
        return "scene";
    }

    /**
     * Sets up the facade implementations by calling static::addImplementation($name, $implementation) for each of the
     * implementations this Facade has.
     *
     * @return void
     */
    protected static function setupFacadeImplementations()
    {
        static::addImplementation('scene-facade', phanda()->create(Factory::class), true);
    }

    /**
     * @param $scene
     * @param array $data
     * @param array $mergeData
     * @return string
     */
    public static function render($scene, $data = [], array $mergeData = [])
    {
        try {
            return static::create($scene, $data, $mergeData)->render();
        } catch (\Throwable $e) {
            /** @var ExceptionHandler $exceptionHandler */
            $exceptionHandler = app()->create(ExceptionHandler::class);
            $exceptionHandler->save($e);
            return $exceptionHandler->render(phanda()->create(Request::class), $e)->send();
        }
    }
}