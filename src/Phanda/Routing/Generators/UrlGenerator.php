<?php

namespace Phanda\Routing\Generators;

use Phanda\Contracts\Routing\Generators\UrlGenerator as UrlGeneratorContract;
use Phanda\Contracts\Routing\Route;
use Phanda\Exceptions\Routing\RouteNotFoundException;
use Phanda\Foundation\Http\Request;
use Phanda\Routing\RouteRepository;
use Phanda\Support\PhandArr;
use Phanda\Support\PhandaStr;

class UrlGenerator implements UrlGeneratorContract
{

    /**
     * @var RouteRepository
     */
    protected $routes;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $cachedScheme;

    /**
     * @var string
     */
    protected $cachedBaseUrl;

    /**
     * @var RouteUrlGenerator
     */
    protected $routeUrlGenerator;

    /**
     * UrlGenerator constructor.
     *
     * @param RouteRepository $routes
     * @param Request $request
     */
    public function __construct(RouteRepository $routes, Request $request)
    {
        $this->routes = $routes;
        $this->setRequest($request);
    }

    /**
     * Gets the current full url.
     *
     * @return string
     */
    public function fullUrl()
    {
        return $this->request->getFullUrl();
    }

    /**
     * Gets the current url.
     *
     * @return string
     */
    public function current()
    {
        return $this->generate($this->request->getPathInfo());
    }

    /**
     * Gets the previous url, with an optional fallback if there is no previous url.
     *
     * @param $fallback
     * @return mixed
     */
    public function previous($fallback = '/')
    {
        $referrer = $this->request->headers->get('referer');
        $url = $referrer ? $this->generate($referrer) : '';

        if ($url) {
            return $this->generate($url);
        }

        return $this->generate($fallback);
    }

    /**
     * Generates a url to a given path.
     *
     * @param string $path
     * @param array $parameters
     * @param bool|null $secure
     * @return string
     */
    public function generate($path, $parameters = [], $secure = null)
    {
        if ($this->isUrlValid($path)) {
            return $path;
        }

        $tail = implode('/', array_map(
                'rawurlencode', (array)$this->formatParameters($parameters))
        );
        $base = $this->formatBaseUrl($this->formatScheme($secure));
        [$path, $query] = $this->extractQueryString($path);

        return $this->formatUrl(
                $base,
                '/' . trim($path . '/' . $tail, '/')
            ) . $query;
    }

    /**
     * Generates a secure url to a given path.
     *
     * @param string $path
     * @param array $parameters
     * @return string
     */
    public function generateSecure($path, $parameters = [])
    {
        return $this->generate($path, $parameters, true);
    }

    /**
     * Generates a url to a given route by its name.
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     *
     * @throws RouteNotFoundException
     */
    public function generateFromRoute($name, $parameters = [], $absolute = true)
    {
        $route = $this->routes->getByName($name);
        if(!is_null($route)) {
            return $this->convertRouteToUrl($route, $parameters, $absolute);
        }

        throw new RouteNotFoundException("Route with name '{$name}' not found.'");
    }

    /**
     * Checks whether a url is valid or not.
     *
     * @param $url
     * @return bool
     */
    public function isUrlValid($url)
    {
        if (!preg_match('~^(#|//|https?://|mailto:|tel:)~', $url)) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        }

        return true;
    }

    /**
     * Formats the given segments into one URL.
     *
     * @param string $base
     * @param string $path
     * @return string
     */
    public function formatUrl($base, $path)
    {
        $path = '/'.trim($path, '/');
        return trim($base.$path, '/');
    }

    /**
     * Sets the internal route repository for generating route urls.
     *
     * @param RouteRepository $routes
     * @return UrlGeneratorContract
     */
    public function setRoutes(RouteRepository $routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @param mixed|array $parameters
     * @return array
     */
    public function formatParameters($parameters)
    {
        $parameters = PhandArr::makeArray($parameters);
        return $parameters;
    }

    /**
     * @param null|bool $secure
     * @return string
     */
    public function formatScheme($secure = null)
    {
        if (!is_null($secure)) {
            return $secure ? 'https://' : 'http://';
        }

        if (is_null($this->cachedScheme)) {
            $this->cachedScheme = $this->request->getScheme() . '://';
        }

        return $this->cachedScheme;
    }

    /**
     * @param string $scheme
     * @param null|string $base
     * @return string
     */
    public function formatBaseUrl($scheme, $base = null)
    {
        if (is_null($base)) {
            if (is_null($this->cachedBaseUrl)) {
                $this->cachedBaseUrl = $this->request->getRootUrl();
            }

            $base = $this->cachedBaseUrl;
        }

        $start = PhandaStr::startsIn('http://', $base) ? 'http://' : 'https://';
        return preg_replace('~' . $start . '~', $scheme, $base, 1);
    }

    /**
     * Extract the query string from the given path.
     *
     * @param  string  $path
     * @return array
     */
    protected function extractQueryString($path)
    {
        if (($queryPosition = strpos($path, '?')) !== false) {
            return [
                substr($path, 0, $queryPosition),
                substr($path, $queryPosition),
            ];
        }

        return [$path, ''];
    }

    /**
     * @param Route $route
     * @param mixed $parameters
     * @param bool $absolute
     * @return string
     */
    protected function convertRouteToUrl(Route $route, $parameters = [], $absolute = true)
    {
        return $this->getRouteUrlGenerator()
            ->generate(
                $route,
                $this->formatParameters($parameters),
                $absolute
            );
    }

    /**
     * @return RouteUrlGenerator
     */
    protected function getRouteUrlGenerator()
    {
        if(is_null($this->routeUrlGenerator)) {
            $this->routeUrlGenerator = new RouteUrlGenerator($this, $this->request);
        }

        return $this->routeUrlGenerator;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->cachedBaseUrl = null;
        $this->cachedScheme = null;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}