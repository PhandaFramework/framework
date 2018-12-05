<?php

namespace Phanda\Providers\Routing;

use Phanda\Contracts\Routing\Router;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Util\Routing\ForwardRouteCallsTrait;

abstract class AbstractRouteServiceProvider extends AbstractServiceProvider
{
    use ForwardRouteCallsTrait;

    /**
     * The controller namespace for the application.
     *
     * @var string|null
     */
    protected $namespace;

    /**
     * @var Router
     */
    protected $router;

    /**
     * Boots the route service provider
     */
    public function boot()
    {
        $this->router = $this->phanda->create(Router::class);
        $this->loadRoutes();
    }

    /**
     * Loads the routes for your application by calling initializeRoutes
     */
    protected function loadRoutes()
    {
        if (method_exists($this, 'initializeRoutes')) {
            $this->phanda->call([$this, 'initializeRoutes']);
        }
    }

    public abstract function initializeRoutes();
}