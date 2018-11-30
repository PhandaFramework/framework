<?php


namespace Phanda\Util\Foundation\Http;


use Exception;
use Phanda\Exceptions\Foundation\Http\HttpResponseException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait ResponseTrait
 * @package Phanda\Util\Foundation\Http
 * @mixin Response
 */
trait ResponseTrait
{
    /**
     * @var mixed
     */
    public $original;

    /**
     * @var \Exception|null
     */
    public $exception;

    /**
     * @return int
     */
    public function status()
    {
        return $this->getStatusCode();
    }

    /**
     * @return string
     */
    public function content()
    {
        return $this->getContent();
    }

    /**
     * @return mixed
     */
    public function getOriginalContent()
    {
        $original = $this->original;

        return $original instanceof self ? $original->{__FUNCTION__}() : $original;
    }

    /**
     * @return mixed
     */
    public function originalContent()
    {
        return $this->getOriginalContent();
    }

    /**
     * @param HeaderBag|array $headers
     * @return $this
     */
    public function addHeaders($headers)
    {
        if ($headers instanceof HeaderBag) {
            $headers = $headers->all();
        }

        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }

        return $this;
    }

    /**
     * @param Cookie|mixed $cookie
     * @return $this
     */
    public function addCookie($cookie)
    {
        $this->headers->setCookie($cookie);

        return $this;
    }

    /**
     * @param Exception $exception
     * @return $this
     */
    public function setException(Exception $exception)
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * @throws HttpResponseException
     */
    public function throwResponse()
    {
        throw new HttpResponseException($this);
    }
}