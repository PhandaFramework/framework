<?php

namespace Phanda\Support\Facades\Routing\Generators;

use Phanda\Contracts\Routing\Generators\UrlGenerator as UrlGeneratorContract;
use Phanda\Foundation\Http\Request;
use Phanda\Routing\RouteRepository;
use Phanda\Support\Facades\Facade;

/**
 * Class UrlGenerator
 * The base UrlGenerator Facade.
 *
 * @package Phanda\Support\Facades\Routing\Generators
 * @see \Phanda\Contracts\Routing\Generators\UrlGenerator
 *
 * @method static string fullUrl()
 * @method static string current()
 * @method static string previous(string $fallback = '')
 * @method static string generate(string $path, array $parameters = [], bool|null $secure = null)
 * @method static string generateSecure(string $path, array $parameters = [])
 * @method static string generateFromRoute(string $name, array $parameters = [], bool $absolute = true)
 * @method static bool isUrlValid(string $url)
 * @method static string formatUrl(string $base, string $path)
 * @method static UrlGeneratorContract setRoutes(RouteRepository $routes)
 * @method static UrlGeneratorContract setRequest(Request $request)
 */
class UrlGenerator extends Facade
{

    /**
     * Set up the name of the Facade instance being resolved. Used internally for checking if the Facade has been
     * resolved or not.
     *
     * @return string
     */
    protected static function getFacadeName(): string
    {
        return "url-generator-facade";
    }

    /**
     * Sets up the facade implementations by calling static::addImplementation($name, $implementation) for each of the
     * implementations this Facade has.
     *
     * @return void
     */
    protected static function setupFacadeImplementations()
    {
        static::addImplementation('url-generator', phanda()->create(UrlGeneratorContract::class));
    }
}