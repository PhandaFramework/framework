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

}