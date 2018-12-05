<?php

namespace Phanda\Contracts\Routing\Validators;

use Phanda\Contracts\Routing\Route;
use Phanda\Foundation\Http\Request;

interface Validator
{
    /**
     * @param Route $route
     * @param Request $request
     * @return bool
     */
    public function matches(Route $route, Request $request);
}