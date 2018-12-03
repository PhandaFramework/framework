<?php

namespace Phanda\Contracts\Foundation;

use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use Phanda\Providers\AbstractServiceProvider;

interface Application extends Container
{

    /**
     * @return string
     */
    public function version();

    /**
     * @return string
     */
    public function basePath();

    /**
     * @return string
     */
    public function environment();

    /**
     * @return bool
     */
    public function inConsole();

    /**
     * @return bool
     */
    public function isDownForMaintenance();

    /**
     * @return void
     */
    public function start();

    /**
     * @param mixed $callback
     * @return void
     */
    public function starting($callback);

    /**
     * @param mixed $callback
     * @return void
     */
    public function started($callback);

    /**
     * @return bool
     */
    public function hasStarted();

    /**
     * @param string|AbstractServiceProvider $provider
     * @param bool $force
     * @return AbstractServiceProvider
     */
    public function register($provider, $force = false);

    /**
     * Stops the application
     */
    public function stop();

    /**
     * Gets the application namespace.
     *
     * @return string
     */
    public function getNamespace();

    /**
     * @return bool
     */
    public function hasBeenBootstrapped();

    /**
     * @param Bootstrap[] $bootstrappers
     */
    public function bootstrapWith($bootstrappers);

    /**
     * @param string $provider
     * @return AbstractServiceProvider
     */
    public function resolveProvider($provider);

}