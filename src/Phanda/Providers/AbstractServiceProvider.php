<?php

namespace Phanda\Providers;

use Phanda\Contracts\Foundation\Application;

abstract class AbstractServiceProvider
{
    /**
     * @var Application
     */
    protected $phanda;

    /**
     * @var bool
     */
    protected $defer = false;

    /**
     * AbstractServiceProvider constructor.
     * @param Application $phanda
     */
    public function __construct($phanda)
    {
        $this->phanda = $phanda;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Get the events that trigger this service provider to register.
     *
     * @return array
     */
    public function when()
    {
        return [];
    }

    /**
     * Determine if the provider is deferred.
     *
     * @return bool
     */
    public function isDeferred()
    {
        return $this->defer;
    }

}