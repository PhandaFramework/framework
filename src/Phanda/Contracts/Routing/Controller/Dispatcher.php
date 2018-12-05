<?php

namespace Phanda\Contracts\Routing\Controller;

use Phanda\Contracts\Routing\Route;
use Phanda\Routing\Controller\AbstractController;

interface Dispatcher
{

    /**
     * @param Route $route
     * @param AbstractController $controller
     * @param string $method
     * @return mixed
     */
    public function dispatch(Route $route, AbstractController $controller, $method);

}