<?php

namespace Phanda\Scene\Engine;

use Closure;
use Phanda\Contracts\Scene\Engine\Engine as BaseEngine;
use Phanda\Exceptions\Scene\Engine\EngineNotFoundException;

class EngineResolver
{
    /**
     * @var array
     */
    protected $engines = [];

    /**
     * @var array
     */
    protected $resolved = [];

    /**
     * The callbacks used to resolve an engine must return an instance that implements
     * Phanda\Contracts\Scene\Engine\Engine
     *
     * @param string $engine
     * @param Closure $callback
     */
    public function registerEngine($engine, Closure $callback)
    {
        unset($this->resolved[$engine]);
        $this->engines[$engine] = $callback;
    }

    /**
     * @param string $engine
     * @return BaseEngine
     *
     * @throws EngineNotFoundException
     */
    public function resolveEngine($engine)
    {
        if(isset($this->resolved[$engine])) {
            return $this->resolved[$engine];
        }

        if(isset($this->engines[$engine])) {
            return $this->resolved[$engine] = $this->engines[$engine]();
        }

        throw new EngineNotFoundException("Engine '{$engine}' could not be resolved, as it wasn't registered at time of resolving.");
    }
}