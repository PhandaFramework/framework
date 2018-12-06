<?php

namespace Phanda\Providers\Routing;

use Phanda\Contracts\Foundation\Application;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Routing\Router;
use Phanda\Contracts\Routing\Controller\Dispatcher as ControllerDispatcherContract;
use Phanda\Routing\Controller\Dispatcher as ControllerDispatcher;

class RoutingServiceProvider extends AbstractServiceProvider
{
    /**
     * Initialises core routing
     */
    public function register()
    {
        $this->registerRouter();
        //$this->registerRoutes();
        $this->registerControllerDispatcher();
    }

    /**
     * Registers the router to phanda
     */
    protected function registerRouter()
    {
        $this->phanda->singleton('router', function($phanda) {
            /** @var Application $phanda */
            return $phanda->create(Router::class);
        });
    }

    protected function registerRoutes()
    {
        $this->phanda->singleton('routes', function($phanda) {
            /** @var Application $phanda */
            /** @var Router $router */
            $router = $phanda[Router::class];
            return $router->getRoutes();
        });
    }

    /**
     * Register the controller dispatcher.
     *
     * @return void
     */
    protected function registerControllerDispatcher()
    {
        $this->phanda->singleton(ControllerDispatcherContract::class, function ($app) {
            return new ControllerDispatcher($app);
        });
    }

}