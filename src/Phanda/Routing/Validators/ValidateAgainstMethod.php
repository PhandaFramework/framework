<?php

namespace Phanda\Routing\Validators;

use Phanda\Contracts\Routing\Route;
use Phanda\Contracts\Routing\Validators\Validator;
use Phanda\Foundation\Http\Request;

class ValidateAgainstMethod implements Validator
{

    /**
     * @param Route $route
     * @param Request $request
     * @return bool
     */
    public function matches(Route $route, Request $request)
    {
        return in_array($request->getMethod(), $route->getHttpMethods());
    }
}