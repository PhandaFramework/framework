<?php

namespace Phanda\Routing\Validators;

use Phanda\Contracts\Routing\Route;
use Phanda\Contracts\Routing\Validators\Validator;
use Phanda\Foundation\Http\Request;

class ValidateAgainstHost implements Validator
{

    /**
     * @param Route $route
     * @param Request $request
     * @return bool
     */
    public function matches(Route $route, Request $request)
    {
        if (is_null($route->getSymfonyCompiledRoute()->getHostRegex())) {
            return true;
        }

        return preg_match($route->getSymfonyCompiledRoute()->getHostRegex(), $request->getHost());
    }
}