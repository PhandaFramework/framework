<?php

namespace Phanda\Routing\Validators;

use Phanda\Contracts\Routing\Route;
use Phanda\Contracts\Routing\Validators\Validator;
use Phanda\Foundation\Http\Request;

class ValidateAgainstScheme implements Validator
{

    /**
     * @param Route $route
     * @param Request $request
     * @return bool
     */
    public function matches(Route $route, Request $request)
    {
        if ($route->isHttpOnly()) {
            return !$request->isSecure();
        } elseif ($route->isHttpsOnly()) {
            return $request->isSecure();
        }

        return true;
    }
}