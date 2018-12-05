<?php

namespace Phanda\Contracts\Routing;

use Phanda\Foundation\Http\Request;
use Symfony\Component\Routing\CompiledRoute;

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
    public function getParametersWithoutNulls();

    /**
     * Get the key / value list of parameters for the route.
     *
     * @return array
     */
    public function getParameters();

    /**
     * @return array
     */
    public function getParameterNames();

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @return array
     */
    public function getRouteDefaults();

    /**
     * @return string
     */
    public function getUri();

    /**
     * @return array
     */
    public function getConditionals();

    /**
     * @param Request $request
     * @return mixed
     */
    public function bindToRequest(Request $request);

    /**
     * @return CompiledRoute
     */
    public function getSymfonyCompiledRoute();

}