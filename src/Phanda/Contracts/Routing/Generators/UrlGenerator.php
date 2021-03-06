<?php

namespace Phanda\Contracts\Routing\Generators;

use Phanda\Foundation\Http\Request;
use Phanda\Routing\RouteRepository;

interface UrlGenerator
{
    /**
     * Gets the current full url.
     *
     * @return string
     */
    public function fullUrl();

    /**
     * Gets the current url.
     *
     * @return string
     */
    public function current();

    /**
     * Gets the previous url, with an optional fallback if there is no previous url.
     *
     * @param $fallback
     * @return string
     */
    public function previous($fallback = '/');

    /**
     * Generates a url to a given path.
     *
     * @param string $path
     * @param array $parameters
     * @param bool|null $secure
     * @return string
     */
    public function generate($path, $parameters = [], $secure = null);

    /**
     * Generates a secure url to a given path.
     *
     * @param string $path
     * @param array $parameters
     * @return string
     */
    public function generateSecure($path, $parameters = []);

    /**
     * Generates a url to a given route by its name.
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    public function generateFromRoute($name, $parameters = [], $absolute = true);

    /**
     * Checks whether a url is valid or not.
     *
     * @param $url
     * @return bool
     */
    public function isUrlValid($url);

    /**
     * Formats the given segments into one URL.
     *
     * @param string $base
     * @param string $path
     * @return string
     */
    public function formatUrl($base, $path);

    /**
     * Sets the internal route repository for generating route urls.
     *
     * @param RouteRepository $routes
     * @return $this
     */
    public function setRoutes(RouteRepository $routes);

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request);

    /**
     * @return Request
     */
    public function getRequest();
}