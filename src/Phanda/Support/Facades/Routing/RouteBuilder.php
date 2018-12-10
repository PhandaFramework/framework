<?php

namespace Phanda\Support\Facades\Routing;

use Phanda\Support\Facades\Facade;

/**
 * Class RouteBuilder
 * The base RouteBuilder facade
 *
 * @package Phanda\Support\Facades\Routing
 * @see RouteBuilder
 *
 * @method static RouteBuilder setUrl(string $url)
 * @method static RouteBuilder anyMethod()
 * @method static RouteBuilder addMethod(string $method)
 * @method static RouteBuilder setMethod(string $method)
 * @method static RouteBuilder setController(string $controller)
 * @method static RouteBuilder setControllerMethod(string $method)
 * @method static RouteBuilder setAction(string $action)
 * @method static RouteBuilder setCallbackAction(\Closure $callback)
 * @method static RouteBuilder setName(string $name)
 * @method static RouteBuilder allowGet()
 * @method static RouteBuilder allowPost()
 * @method static RouteBuilder allowHead()
 * @method static RouteBuilder allowPut()
 * @method static RouteBuilder allowPatch()
 * @method static RouteBuilder allowDelete()
 * @method static RouteBuilder allowOptions()
 * @method static RouteBuilder setScene(string $scene, array $data = [])
 * @method static RouteBuilder build()
 * @method static RouteBuilder newRoute()
 */
class RouteBuilder extends Facade
{
    /**
     * Set up the name of the Facade instance being resolved. Used internally for checking if the Facade has been
     * resolved or not.
     *
     * @return string
     */
    protected static function getFacadeName(): string
    {
        return 'route-builder';
    }

    /**
     * Sets up the facade implementations by calling static::addImplementation($name, $implementation) for each of the
     * implementations this Facade has.
     *
     * @return void
     */
    protected static function setupFacadeImplementations()
    {
        static::addImplementation('route-builder-facade', \Phanda\Routing\RouteBuilder::class);
    }
}