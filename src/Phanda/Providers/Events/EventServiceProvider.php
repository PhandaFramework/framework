<?php


namespace Phanda\Providers\Events;

use Phanda\Events\Dispatcher;
use Phanda\Providers\AbstractServiceProvider;

class EventServiceProvider extends AbstractServiceProvider
{

    public function register()
    {
        $this->phanda->singleton('events', function($app) {
           return new Dispatcher($app);
        });
    }

}