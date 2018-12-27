<?php


namespace Phanda\Foundation\Http;

use Closure;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Routing\Route;
use Phanda\Support\PhandArr;
use Phanda\Util\Foundation\Http\RequestContentTypes;
use Phanda\Util\Foundation\Http\RequestInput;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest implements Arrayable, \ArrayAccess
{
    use RequestContentTypes,
        RequestInput;

    /**
     * @var Closure
     */
    protected $routeResolver;

    /**
     * @return array
     */
    public function toArray()
    {
        return [];
    }

    /**
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return false;
    }

    /**
     * @param mixed $offset
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {

    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {

    }

    /**
     * Create a new Illuminate HTTP request from server variables.
     *
     * @return static
     */
    public static function capture()
    {
        static::enableHttpMethodParameterOverride();

        return static::createFromSymfonyRequest(SymfonyRequest::createFromGlobals());
    }

    /**
     * @param SymfonyRequest $request
     * @return Request
     */
    public static function createFromSymfonyRequest(SymfonyRequest $request)
    {
        if ($request instanceof static) {
            return $request;
        }

        $content = $request->content;

        $request = (new static)->duplicate(
            $request->query->all(), $request->request->all(), $request->attributes->all(),
            $request->cookies->all(), $request->files->all(), $request->server->all()
        );

        $request->content = $content;

        $request->request = $request->getInputSource();

        return $request;
    }

    /**
     * @inheritdoc
     */
    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
    {
        return parent::duplicate($query, $request, $attributes, $cookies, $this->filterFiles($files), $server);
    }

    /**
     * @param  mixed  $files
     * @return mixed
     */
    protected function filterFiles($files)
    {
        if (!$files) {
            return;
        }

        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $files[$key] = $this->filterFiles($files[$key]);
            }

            if (empty($files[$key])) {
                unset($files[$key]);
            }
        }

        return;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected function getInputSource()
    {
        if ($this->isJson()) {
            return $this->json();
        }

        return in_array($this->getRealMethod(), ['GET', 'HEAD']) ? $this->query : $this->request;
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern == '' ? '/' : $pattern;
    }

    /**
     * Get the current decoded path info for the request.
     *
     * @return string
     */
    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    /**
     * Set the route resolver callback.
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function setRouteResolver(Closure $callback)
    {
        $this->routeResolver = $callback;
        return $this;
    }

	/**
	 * @return Closure
	 */
	public function getRouteResolver(): Closure
	{
		return $this->routeResolver;
	}

	/**
	 * Get the route handling the request.
	 *
	 * @param  string|null  $param
	 * @param  mixed   $default
	 * @return Route|object|string
	 */
	public function route($param = null, $default = null)
	{
		/** @var Route $route */
		$route = call_user_func($this->getRouteResolver());

		if (is_null($route) || is_null($param)) {
			return $route;
		}

		return $route->getParameter($param, $default);
	}

    /**
     * Gets the current url
     *
     * @return string
     */
    public function getUrl()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }

    /**
     * Gets the current url including the query
     *
     * @return string
     */
    public function getFullUrl()
    {
        $query = $this->getQueryString();
        $question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';
        return $query ? $this->getUrl().$question.$query : $this->getUrl();
    }

    /**
     * @return string
     */
    public function getRootUrl()
    {
        return rtrim($this->getSchemeAndHttpHost().$this->getBaseUrl(), '/');
    }


	/**
	 * Get an input element from the request.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return PhandArr::get($this->all(), $key, function () use ($key) {
			return $this->route($key);
		});
	}

}