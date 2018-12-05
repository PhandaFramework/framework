<?php

namespace Phanda\Routing\Controller;

use Phanda\Foundation\Http\Response;

abstract class AbstractController
{
    /**
     * Execute a method on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return Response
     */
    public function callRouteMethod($method, $parameters)
    {
        return call_user_func_array([$this, $method], $parameters);
    }
}