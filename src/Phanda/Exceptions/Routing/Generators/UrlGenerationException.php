<?php

namespace Phanda\Exceptions\Routing\Generators;

use Phanda\Contracts\Routing\Route;
use Phanda\Exceptions\FatalPhandaException;

class UrlGenerationException extends FatalPhandaException
{

    public static function missingRouteParameters(Route $route)
    {
        return new static("Missing required parameters for Route: '{$route->getName()}', on URI: '{$route->getUri()}'.");
    }

}