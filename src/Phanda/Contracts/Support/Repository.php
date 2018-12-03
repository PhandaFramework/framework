<?php

namespace Phanda\Contracts\Support;

interface Repository
{
    /**
     * @param  string  $key
     * @return bool
     */
    public function has($key);

    /**
     * @param  array|string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @return array
     */
    public function all();

    /**
     * @param  array|string  $key
     * @param  mixed   $value
     * @return void
     */
    public function set($key, $value = null);

    /**
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function prepend($key, $value);

    /**
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function push($key, $value);
}