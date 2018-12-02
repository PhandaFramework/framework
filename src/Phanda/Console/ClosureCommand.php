<?php

namespace Phanda\Console;

use Closure;

class ClosureCommand extends ConsoleCommand
{
    /**
     * @var Closure
     */
    protected $callback;

    public function __construct($signature, Closure $callback)
    {
        $this->signature = $signature;
        $this->callback = $callback;
        parent::__construct();
    }
}