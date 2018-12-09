<?php

namespace Phanda\Routing\Generators;

use Phanda\Contracts\Routing\Route;
use Phanda\Exceptions\Routing\Generators\UrlGenerationException;
use Phanda\Foundation\Http\Request;
use Phanda\Support\PhandArr;
use Phanda\Support\PhandaStr;

class RouteUrlGenerator
{
    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var Request
     */
    protected $request;

    /**
     * The named parameter defaults.
     *
     * @var array
     */
    public $defaultParameters = [];

    /**
     * Characters that should not be URL encoded.
     *
     * @var array
     */
    public $dontEncode = [
        '%2F' => '/',
        '%40' => '@',
        '%3A' => ':',
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
        '%3F' => '?',
        '%26' => '&',
        '%23' => '#',
        '%25' => '%',
    ];

    /**
     * RouteUrlGenerator constructor.
     *
     * @param UrlGenerator $urlGenerator
     * @param Request $request
     */
    public function __construct(UrlGenerator $urlGenerator, Request $request)
    {
        $this->urlGenerator = $urlGenerator;
        $this->request = $request;
    }

    /**
     * @param Route $route
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    public function generate(Route $route, $parameters = [], $absolute = true): string
    {
        $domain = $this->getRouteDomain($route);

        $uri = $this->addQueryStringToUri(
            $this->urlGenerator->formatUrl(
                $base = $this->replaceBaseParameters($route, $domain, $parameters),
                $this->replaceRouteParameters($route->getUri(), $parameters)
            ),
            $parameters
        );

        if (preg_match('/\{.*?\}/', $uri)) {
            throw UrlGenerationException::missingRouteParameters($route);
        }

        $uri = strtr(rawurlencode($uri), $this->dontEncode);

        if (!$absolute) {
            $uri = preg_replace('#^(//|[^/?])+#', '', $uri);
            if ($base = $this->request->getBaseUrl()) {
                $uri = preg_replace('#^' . $base . '#i', '', $uri);
            }
            return '/' . ltrim($uri, '/');
        }

        return $uri;
    }

    /**
     * @param Route $route
     * @return string|null
     */
    protected function getRouteDomain(Route $route)
    {
        return $route->getDomain() ? $this->formatRouteDomain($route) : null;
    }

    /**
     * @param Route $route
     * @return string
     */
    protected function formatRouteDomain(Route $route)
    {
        return $this->addPortToDomain($this->getRouteScheme($route) . $route->getDomain());
    }

    /**
     * @param Route $route
     * @return string
     */
    protected function getRouteScheme(Route $route)
    {
        if ($route->isHttpOnly()) {
            return "http://";
        } elseif ($route->isHttpsOnly()) {
            return "https://";
        }

        return $this->urlGenerator->formatScheme();
    }

    /**
     * @param $domain
     * @return string
     */
    protected function addPortToDomain($domain)
    {
        $secure = $this->request->isSecure();
        $port = $this->request->getPort();

        return ($secure && $port === 443) || (!$secure && $port === 80)
            ? $domain : $domain . ':' . $port;
    }

    /**
     * @param $uri
     * @param array $parameters
     * @return string
     */
    protected function addQueryStringToUri($uri, array $parameters)
    {
        if (!is_null($fragment = parse_url($uri, PHP_URL_FRAGMENT))) {
            $uri = preg_replace('/#.*/', '', $uri);
        }

        $uri .= $this->getRouteQueryString($parameters);
        return is_null($fragment) ? $uri : $uri . "#{$fragment}";
    }

    /**
     * @param array $parameters
     * @return string
     */
    protected function getRouteQueryString(array $parameters)
    {
        if (count($parameters) === 0) {
            return '';
        }

        $query = PhandArr::convertToQueryString(
            $keyed = $this->getStringParameters($parameters)
        );

        if (count($keyed) < count($parameters)) {
            $query .= '&' . implode('&', $this->getNumericParameters($parameters));
        }

        return '?' . trim($query, '&');
    }

    /**
     * Get the string parameters from a given list.
     *
     * @param  array $parameters
     * @return array
     */
    protected function getStringParameters(array $parameters)
    {
        return array_filter($parameters, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get the numeric parameters from a given list.
     *
     * @param  array $parameters
     * @return array
     */
    protected function getNumericParameters(array $parameters)
    {
        return array_filter($parameters, 'is_numeric', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Set the default named parameters used by the URL generator.
     *
     * @param  array $defaults
     * @return void
     */
    public function defaults(array $defaults)
    {
        $this->defaultParameters = array_merge(
            $this->defaultParameters, $defaults
        );
    }

    /**
     * Replace the parameters on the root path.
     *
     * @param  Route $route
     * @param  string $domain
     * @param  array $parameters
     * @return string
     */
    protected function replaceBaseParameters(Route $route, $domain, &$parameters)
    {
        $scheme = $this->getRouteScheme($route);

        return $this->replaceRouteParameters(
            $this->urlGenerator->formatBaseUrl($scheme, $domain), $parameters
        );
    }

    /**
     * Replace all of the wildcard parameters for a route path.
     *
     * @param  string $path
     * @param  array $parameters
     * @return string
     */
    protected function replaceRouteParameters($path, array &$parameters)
    {
        $path = $this->replaceNamedParameters($path, $parameters);

        $path = preg_replace_callback('/\{.*?\}/', function ($match) use (&$parameters) {
            return (empty($parameters) && !PhandaStr::endsIn('?}', $match[0]))
                ? $match[0]
                : array_shift($parameters);
        }, $path);

        return trim(preg_replace('/\{.*?\?\}/', '', $path), '/');
    }

    /**
     * Replace all of the named parameters in the path.
     *
     * @param  string $path
     * @param  array $parameters
     * @return string
     */
    protected function replaceNamedParameters($path, &$parameters)
    {
        return preg_replace_callback('/\{(.*?)\??\}/', function ($m) use (&$parameters) {
            if (isset($parameters[$m[1]])) {
                return PhandArr::take($parameters, $m[1]);
            } elseif (isset($this->defaultParameters[$m[1]])) {
                return $this->defaultParameters[$m[1]];
            }

            return $m[0];
        }, $path);
    }
}