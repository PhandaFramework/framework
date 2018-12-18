<?php

namespace Phanda\Support\Facades\Routing;

use Phanda\Support\Facades\Facade;

/**
 * Class RouteBuilder
 * The base RouteBuilder facade
 *
 * @package Phanda\Support\Facades\Routing
 * @see \Phanda\Routing\RouteBuilder
 *
 * @method static \Phanda\Routing\RouteBuilder setUrl(string $url)
 * @method static \Phanda\Routing\RouteBuilder anyMethod()
 * @method static \Phanda\Routing\RouteBuilder addMethod(string $method)
 * @method static \Phanda\Routing\RouteBuilder setMethod(string $method)
 * @method static \Phanda\Routing\RouteBuilder setController(string $controller)
 * @method static \Phanda\Routing\RouteBuilder setControllerMethod(string $method)
 * @method static \Phanda\Routing\RouteBuilder setAction(string $action)
 * @method static \Phanda\Routing\RouteBuilder setCallbackAction(\Closure $callback)
 * @method static \Phanda\Routing\RouteBuilder setName(string $name)
 * @method static \Phanda\Routing\RouteBuilder allowGet()
 * @method static \Phanda\Routing\RouteBuilder allowPost()
 * @method static \Phanda\Routing\RouteBuilder allowHead()
 * @method static \Phanda\Routing\RouteBuilder allowPut()
 * @method static \Phanda\Routing\RouteBuilder allowPatch()
 * @method static \Phanda\Routing\RouteBuilder allowDelete()
 * @method static \Phanda\Routing\RouteBuilder allowOptions()
 * @method static \Phanda\Routing\RouteBuilder setScene(string $scene, array $data = [])
 * @method static \Phanda\Routing\RouteBuilder build()
 * @method static \Phanda\Routing\RouteBuilder newRoute()
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