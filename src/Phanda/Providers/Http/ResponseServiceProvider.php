<?php

namespace Phanda\Providers\Http;

use Phanda\Contracts\Foundation\Application;
use Phanda\Http\ResponseManager;
use Phanda\Contracts\Http\ResponseManager as ResponseManagerContract;
use Phanda\Providers\AbstractServiceProvider;

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
           $responseManager = new ResponseManager();
           return $responseManager;
        });

        $this->phanda->alias('response-manager', ResponseManager::class);
        $this->phanda->alias('response-manager', ResponseManagerContract::class);
    }

}