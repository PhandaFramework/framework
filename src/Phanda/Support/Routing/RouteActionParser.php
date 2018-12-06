<?php

namespace Phanda\Support\Routing;

use Phanda\Support\PhandArr;
use Phanda\Support\PhandaStr;
use UnexpectedValueException;

class RouteActionParser
{

    /**
     * @param string $uri
     * @param mixed $action
     * @return array
     */
    public static function parse($uri, $action)
    {
        if (is_null($action)) {
            return static::noActionFound($uri);
        }

        if (is_callable($action)) {
            return !is_array($action) ? ['method' => $action] : [
                'method' => $action[0] . '@' . $action[1],
                'controller' => $action[0] . '@' . $action[1],
            ];
        } elseif (!isset($action['method'])) {
            $action['method'] = static::findCallback($action);
        }

        if (is_string($action['method']) && !PhandaStr::contains('@', $action['method'])) {
            $action['method'] = static::makeInvokable($action['method']);
        }

        return $action;
    }

    /**
     * @param $uri
     * @return array
     */
    protected static function noActionFound($uri)
    {
        return ['method' => function () use ($uri) {
            throw new \LogicException("No action supplied for [{$uri}]");
        }];
    }

    /**
     * @param array $action
     * @return callable
     */
    protected static function findCallback(array $action)
    {
        return PhandArr::first($action, function ($value, $key) {
            return is_callable($value) && is_numeric($key);
        });
    }

    /**
     * @param $action
     * @return string
     */
    protected static function makeInvokable($action)
    {
        if (!method_exists($action, '__invoke')) {
            throw new UnexpectedValueException("Invalid action supplied: [{$action}].");
        }

        return $action . '@__invoke';
    }

}