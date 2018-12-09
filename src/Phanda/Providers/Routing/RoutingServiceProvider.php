<?php

namespace Phanda\Providers\Routing;

use Phanda\Contracts\Foundation\Application;
use Phanda\Foundation\Http\Request;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Routing\Generators\UrlGenerator;
use Phanda\Routing\Router;
use Phanda\Contracts\Routing\Controller\Dispatcher as ControllerDispatcherContract;
use Phanda\Routing\Controller\Dispatcher as ControllerDispatcher;
use Phanda\Routing\RouteRepository;

class RoutingServiceProvider extends AbstractServiceProvider
{
    /**
     * Initialises core routing
     */
    public function register()
    {
        $this->registerRouter();
        $this->registerUrlGenerator();
        $this->registerControllerDispatcher();
    }

    /**
     * Registers the router to phanda
     */
    protected function registerRouter()
    {
        $this->phanda->singleton('router', function ($phanda) {
            /** @var Application $phanda */
            return $phanda->create(Router::class);
        });
        $this->phanda->alias('router', \Phanda\Contracts\Routing\Router::class);
    }

    /**
     * Registers the Url Generator, and the respective routes.
     */
    protected function registerUrlGenerator()
    {
        $this->phanda->singleton('url', function ($phanda) {
            /** @var Application $phanda */
            /** @var Router $router */
            $router = $phanda->create('router');
            $routes = $router->getRoutes();
            $phanda->instance('routes', $routes);

            $urlGenerator = new UrlGenerator(
                $routes,
                $phanda->onReattach('request', $this->urlRequestAttachmentCallback())
            );

            $phanda->onReattach('routes', function(Application $phanda, RouteRepository $routes) {
                /** @var UrlGenerator $urlGenerator */
                $urlGenerator = $phanda->create(UrlGenerator::class);
                $urlGenerator->setRoutes($routes);
            });

            return $urlGenerator;
        });

        $this->phanda->alias('url', \Phanda\Contracts\Routing\Generators\UrlGenerator::class);
        $this->phanda->alias('url', UrlGenerator::class);
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

    /**
     * @return \Closure
     */
    protected function urlRequestAttachmentCallback()
    {
        return function(Application $phanda, Request $request) {
            /** @var UrlGenerator $urlGenerator */
            $urlGenerator = $phanda->create(UrlGenerator::class);
            $urlGenerator->setRequest($request);
        };
    }

}