<?php


namespace Phanda\Foundation\Http;

use Phanda\Contracts\Support\Arrayable;
use Phanda\Util\Foundation\Http\RequestContentTypes;
use Phanda\Util\Foundation\Http\RequestInput;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest implements Arrayable, \ArrayAccess
{
    use RequestContentTypes,
        RequestInput;

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

    /**@param  mixed  $files
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

}