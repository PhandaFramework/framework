<?php

namespace Phanda\Foundation\Bootstrap;

use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use Phanda\Foundation\Application;

class BootstrapProviders implements Bootstrap
{
    /**
     * @param Application $phanda
     * @return void
     */
    public function bootstrap(Application $phanda)
    {
        $phanda->registerProvidersInConfiguration();
    }
}