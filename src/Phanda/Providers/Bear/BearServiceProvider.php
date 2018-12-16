<?php

namespace Phanda\Providers\Bear;

use Phanda\Providers\AbstractServiceProvider;

use Phanda\Contracts\Bear\Query\Builder as QueryBuilderContact;
use Phanda\Bear\Query\Builder as QueryBuilder;

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
        $this->phanda->attach(QueryBuilderContact::class, QueryBuilder::class);
    }

}