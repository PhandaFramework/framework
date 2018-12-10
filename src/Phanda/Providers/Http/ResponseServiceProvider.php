<?php

namespace Phanda\Providers\Http;

use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Routing\Generators\UrlGenerator;
use Phanda\Http\ResponseManager;
use Phanda\Contracts\Http\ResponseManager as ResponseManagerContract;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Scene\Factory;

class ResponseServiceProvider extends AbstractServiceProvider
{

    /**
     * Handles the registration of anything to do with the response.
     */
    public function register()
    {
        $this->registerResponseManager();
    }

    /**
     * Registers the ResponseManager and it's respective aliases.
     */
    protected function registerResponseManager()
    {
        $this->phanda->attach('response-manager', function($phanda) {
           /** @var Application $phanda */
           /** @var Factory $sceneFactory */
           $sceneFactory = $phanda->create(Factory::class);
           /** @var UrlGenerator $urlGenerator */
           $urlGenerator = $phanda->create(UrlGenerator::class);
           $responseManager = new ResponseManager($sceneFactory, $urlGenerator);
           return $responseManager;
        });

        $this->phanda->alias('response-manager', ResponseManager::class);
        $this->phanda->alias('response-manager', ResponseManagerContract::class);
    }

}