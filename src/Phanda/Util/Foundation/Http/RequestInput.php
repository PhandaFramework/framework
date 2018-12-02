<?php


namespace Phanda\Util\Foundation\Http;

use Phanda\Foundation\Http\Request;

/**
 * Trait RequestInput
 * @package Phanda\Util\Foundation\Http
 * @mixin Request
 */
trait RequestInput
{
    /**
     * @param string $source
     * @param string $key
     * @param string|array|null $default
     * @return string|array|null
     */
    protected function retrieveItem($source, $key, $default)
    {
        if (is_null($key)) {
            return $this->$source->all();
        }

        return $this->$source->get($key, $default);
    }

    /**
     * @param string $key
     * @param string|array|null $default
     * @return string|array|null
     */
    public function header($key = null, $default = null)
    {
        return $this->retrieveItem('headers', $key, $default);
    }

}