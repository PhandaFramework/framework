<?php

namespace Phanda\Providers\Routing;

use Phanda\Contracts\Foundation\Application;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Routing\Router;

class RoutingServiceProvider extends AbstractServiceProvider
{

    /**
     * Initialises core routing
     */
    public function register()
    {
        $this->registerRouter();
    }

    /**
     * Registers the router to phanda
     */
    public function registerRouter()
    {
        $this->phanda->singleton('router', function($phanda) {
            /** @var Application $phanda */
            return $phanda->create(Router::class);
        });
    }

}