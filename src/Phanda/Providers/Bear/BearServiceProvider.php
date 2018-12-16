<?php

namespace Phanda\Providers\Bear;

use Phanda\Providers\AbstractServiceProvider;

class BearServiceProvider extends AbstractServiceProvider
{

    /**
     * Registers the BearORM and it's related classes
     */
    public function register()
    {
        $this->registerBearQueryBuilder();
    }

    public function registerBearQueryBuilder()
    {

    }

}