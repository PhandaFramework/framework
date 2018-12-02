<?php

namespace Phanda\Console\Events;

use Phanda\Console\Application;
use Phanda\Events\Event;

class KungfuStartingEvent extends Event
{
    /**
     * The Kungfy application instance.
     *
     * @var Application
     */
    public $kungfu;

    /**
     * Create a new event instance.
     *
     * @param  Application $kungfu
     * @return void
     */
    public function __construct($kungfu)
    {
        $this->kungfu = $kungfu;
    }
}