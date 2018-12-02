<?php


namespace Phanda\Util\Foundation\Http;


use Phanda\Foundation\Http\Request;
use Phanda\Support\PhandaStr;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Trait RequestContentTypes
 * @package Phanda\Util\Foundation\Http
 * @mixin Request
 */
trait RequestContentTypes
{

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson()
    {
        return PhandaStr::contains(['/json', '+json'], $this->header('CONTENT_TYPE'));
    }

    /**
     * Get the JSON payload for the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return \Symfony\Component\HttpFoundation\ParameterBag|mixed
     */
    public function json($key = null, $default = null)
    {
        if (! isset($this->json)) {
            $this->json = new ParameterBag((array) json_decode($this->getContent(), true));
        }

        if (is_null($key)) {
            return $this->json;
        }

        return data_get($this->json->all(), $key, $default);
    }

}