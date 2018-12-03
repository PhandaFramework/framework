<?php

namespace Phanda\Providers;

use Phanda\Foundation\Application;

class ServiceProviderRepository
{
    /**
     * @var Application
     */
    private $phanda;

    /**
     * ServiceProviderRepository constructor.
     * @param Application $phanda
     */
    public function __construct(Application $phanda)
    {
        $this->phanda = $phanda;
    }

    /**
     * @param AbstractServiceProvider[] $providers
     */
    public function loadProviders(array $providers)
    {
        foreach($providers as $provider) {
            /** @var AbstractServiceProvider $provider */
            $provider = new $provider($this->phanda);

            if($provider->isDeferred()) {
                // TODO: Implement deferred service providers
            } else {
                $this->phanda->register($provider);
            }
        }
    }

}