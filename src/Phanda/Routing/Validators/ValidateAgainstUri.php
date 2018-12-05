<?php

namespace Phanda\Routing\Validators;

use Phanda\Contracts\Routing\Route;
use Phanda\Contracts\Routing\Validators\Validator;
use Phanda\Foundation\Http\Request;

class ValidateAgainstUri implements Validator
{

    /**
     * @param Route $route
     * @param Request $request
     * @return bool
     */
    public function matches(Route $route, Request $request)
    {
        $path = $request->path() === '/' ? '/' : '/'.$request->path();
        return preg_match($route->getSymfonyCompiledRoute()->getRegex(), rawurldecode($path));
    }
}