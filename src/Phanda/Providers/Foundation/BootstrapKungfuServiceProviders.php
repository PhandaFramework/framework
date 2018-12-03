<?php

namespace Phanda\Providers\Foundation;

use Phanda\Providers\AbstractServiceProviderCollection;

class BootstrapKungfuServiceProviders extends AbstractServiceProviderCollection
{
    protected $providers = [
        KungfuServiceProvider::class
    ];
}