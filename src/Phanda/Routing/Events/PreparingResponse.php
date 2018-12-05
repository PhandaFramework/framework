<?php

namespace Phanda\Routing\Events;

use Phanda\Contracts\Routing\Route;
use Phanda\Events\Event;
use Phanda\Foundation\Http\Request;

class PreparingResponse extends Event
{
    /**
     * The route instance.
     *
     * @var Route
     */
    public $route;

    /**
     * The request instance.
     *
     * @var Request
     */
    public $request;

    /**
     * PreparingResponse constructor.
     *
     * @param Route $route
     * @param Request $request
     */
    public function __construct(Route $route, Request $request)
    {
        $this->route = $route;
        $this->request = $request;
    }
}