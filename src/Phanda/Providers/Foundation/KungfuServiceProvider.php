<?php

namespace Phanda\Providers\Foundation;

use Phanda\Foundation\Console\Commands\ServeCommand;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Console\Application as Kungfu;

class KungfuServiceProvider extends AbstractServiceProvider
{

    protected $defer = true;

    protected $commands = [

    ];

    protected $devCommands = [
        'Serve' => 'commands.serve'
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
        foreach (array_keys($commands) as $command) {
            call_user_func_array([$this, "register{$command}Command"], []);
        }

        $this->commands(array_values($commands));
    }

    protected function registerServeCommand()
    {
        $this->app->singleton('command.serve', function () {
            return new ServeCommand;
        });
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