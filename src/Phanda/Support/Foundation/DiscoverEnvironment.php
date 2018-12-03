<?php

namespace Phanda\Support\Foundation;

use Closure;
use Phanda\Support\PhandArr;
use Phanda\Support\PhandaStr;

class DiscoverEnvironment
{

    /**
     * @param Closure $callback
     * @param null|array $consoleArgs
     * @return string
     */
    public function discover(Closure $callback, $consoleArgs = null)
    {
        if($consoleArgs !== null) {
            return $this->discoverConsoleEnvironment($callback, $consoleArgs);
        }

        return $this->discoverWebEnvironment($callback);
    }

    /**
     * @param Closure $callback
     * @return string
     */
    protected function discoverWebEnvironment(Closure $callback)
    {
        return $callback();
    }

    /**
     * @param Closure $callback
     * @param array $consoleArgs
     * @return string
     */
    protected function discoverConsoleEnvironment(Closure $callback, array $consoleArgs)
    {
        if (! is_null($value = $this->getConsoleEnvironmentArgument($consoleArgs))) {
            return reset(array_slice(explode('=', $value), 1));
        }

        return $this->discoverWebEnvironment($callback);
    }

    /**
     * @param array $consoleArgs
     * @return string|null
     */
    protected function getConsoleEnvironmentArgument(array $consoleArgs)
    {
        return PhandArr::first($consoleArgs, function ($value) {
            return PhandaStr::startsIn($value, '--env');
        });
    }

}