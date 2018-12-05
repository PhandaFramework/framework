<?php

namespace Phanda\Routing;

use Phanda\Contracts\Support\Repository;
use Phanda\Support\PhandArr;
use Traversable;

class RouteRepository implements Repository, \Countable, \IteratorAggregate
{

    /** @var \Phanda\Contracts\Routing\Route[] */
    protected $routes = [];

    /**
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return PhandArr::has($this->routes, $key);
    }

    /**
     * @param  array|string $key
     * @param  mixed $default
     * @return \Phanda\Contracts\Routing\Route|\Phanda\Contracts\Routing\Route[]
     */
    public function get($key, $default = null)
    {
        return PhandArr::get($this->routes, $key, $default);
    }

    /**
     * @return \Phanda\Contracts\Routing\Route[]
     */
    public function all()
    {
        return $this->routes;
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
            PhandArr::set($this->routes, $key, $value);
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

    /**
     * @return Traversable|\Phanda\Contracts\Routing\Route[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->routes);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->routes);
    }
}