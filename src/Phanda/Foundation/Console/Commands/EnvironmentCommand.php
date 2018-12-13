<?php

namespace Phanda\Foundation\Console\Commands;

use Phanda\Console\ConsoleCommand;
use Phanda\Util\Console\Commands\ConfirmIfInProductionTrait;

class EnvironmentCommand extends ConsoleCommand
{
    use ConfirmIfInProductionTrait;

    protected $signature = 'app:environment {environment?}';

    protected $description = "Gets or sets the current application environment.";

    public function handle()
    {
        $environment = $this->getInputArgument('environment');
        if(is_null($environment)) {
            $this->showEnvironment();
        } else {
            $this->setEnvironment($environment);
        }
    }

    protected function showEnvironment()
    {
        $this->line("<info>Current Environment: </info>" . config('phanda.environment'));
    }

    protected function setEnvironment($environment)
    {
        $current = config('phanda.environment');
        $this->line("<info>Changing environment from '{$current}' to '{$environment}'</info>");

        if($this->confirmToProceed()) {
            file_put_contents(
                environment_path(phanda()->getAppEnvironmentFile()),
                preg_replace(
                    $this->environmentReplacementPattern(),
                    'APPLICATION_ENVIRONMENT=' . $environment,
                    file_get_contents(environment_path(phanda()->getAppEnvironmentFile()))
                )
            );

            $this->line("<info>Environment changed successfully.</info>");
        }
    }

    protected function environmentReplacementPattern()
    {
        $escaped = preg_quote('='.config('phanda.environment'), '/');
        return "/^APPLICATION_ENVIRONMENT{$escaped}/m";
    }
}