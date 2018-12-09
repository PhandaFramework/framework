<?php

namespace Phanda\Contracts\Routing\Generators;

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
     * @return mixed
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
}