<?php

namespace Phanda\Configuration;

use Phanda\Contracts\Support\Repository as RepositoryContract;
use Phanda\Support\PhandArr;

class Repository implements RepositoryContract
{

    protected $configurationItems = [];

    /**
     * Repository constructor.
     * @param array $configurationItems
     */
    public function __construct(array $configurationItems = [])
    {
        $this->configurationItems = $configurationItems;
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return PhandArr::has($this->configurationItems, $key);
    }

    /**
     * @param  array|string $key
     * @param  mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if(is_array($key)) {
            return $this->getAll($key);
        }

        return PhandArr::get($this->configurationItems, $key, $default);
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getAll($keys)
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                [$key, $default] = [$default, null];
            }

            $config[$key] = PhandArr::get($this->configurationItems, $key, $default);
        }

        return $config;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->configurationItems;
    }

    /**
     * @param  array|string $key
     * @param  mixed $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            PhandArr::set($this->configurationItems, $key, $value);
        }
    }

    /**
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);
        array_unshift($array, $value);
        $this->set($key, $array);
    }

    /**
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key);
        $array[] = $value;
        $this->set($key, $array);
    }
}