<?php

namespace Phanda\Providers\Foundation;

use Phanda\Foundation\Console\Commands\ApplicationDebugCommand;
use Phanda\Foundation\Console\Commands\EnvironmentCommand;
use Phanda\Foundation\Console\Commands\ServeCommand;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Console\Application as Kungfu;

class KungfuServiceProvider extends AbstractServiceProvider
{

    protected $defer = true;

    protected $commands = [

    ];

    protected $devCommands = [
        'command.debug' => ApplicationDebugCommand::class,
        'command.environment' => EnvironmentCommand::class,
        'command.serve' => ServeCommand::class
    ];

    public function register()
    {
        $this->registerCommands(
            array_merge($this->commands, $this->devCommands)
        );
    }

    /**
     * @param array $commands
     */
    protected function registerCommands(array $commands)
    {
        foreach ($commands as $key => $command) {
            $this->phanda->singleton($key, function() use ($command) {
               return new $command;
            });
        }

        $this->commands(array_values($commands));
    }

    /**
     * Registers the commands with Kungfu
     * @param $commands
     */
    public function commands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        Kungfu::starting(function ($kungfu) use ($commands) {
            /** @var Kungfu $kungfu */
            $kungfu->resolveCommands($commands);
        });
    }

}