<?php

namespace Phanda\Foundation\Bootstrap;

use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use Phanda\Foundation\Application;
use Phanda\Support\Facades\Facade;
use Phanda\Configuration\Repository as ConfigurationRepository;

class BootstrapFacades implements Bootstrap
{
    /**
     * @var Application
     */
    protected $phanda;

    /**
     * @param Application $phanda
     * @return void
     */
    public function bootstrap(Application $phanda)
    {
        $this->phanda = $phanda;

        $this->initializeFacades();
        $this->loadApplicationFacades();
    }

    protected function initializeFacades()
    {
        Facade::setFacadePhandaInstance($this->phanda);
    }

    protected function loadApplicationFacades()
    {
        // TODO: Implement Facade Caching
    }
}