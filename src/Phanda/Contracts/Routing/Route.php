<?php


namespace Phanda\Contracts\Routing;


interface Route
{

    /**
     * Perform route action, return result.
     *
     * @return mixed
     */
    public function run();

    /**
     * Get the key / value list of parameters without null values.
     *
     * @return array
     */
    public function parametersWithoutNulls();

    /**
     * Get the key / value list of parameters for the route.
     *
     * @return array
     */
    public function parameters();

}