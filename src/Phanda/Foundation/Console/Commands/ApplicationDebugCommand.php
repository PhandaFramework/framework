<?php

namespace Phanda\Foundation\Console\Commands;

use Phanda\Console\ConsoleCommand;
use Phanda\Util\Console\Commands\ConfirmIfInProductionTrait;
use Symfony\Component\Console\Input\InputArgument;

class ApplicationDebugCommand extends ConsoleCommand
{
    use ConfirmIfInProductionTrait;

    protected $name = "app:debug";

    protected $description = "Gets or sets the application debug mode.";

    protected $allowedTrueModes = ['true', 't', 'enable', 'e'];

    protected $allowedFalseModes = ['false', 'f', 'disable', 'd'];

    public function handle()
    {
        $mode = $this->getInputArgument('mode');
        if (!is_null($mode)) {
            if ($this->validateModeArgument($mode)) {
                if (in_array($mode, $this->allowedTrueModes)) {
                    $this->enableDebugMode();
                } else {
                    $this->disableDebugMode();
                }
            }
        } else {
            $this->outputDebugStatus();
        }
    }

    protected function outputDebugStatus()
    {
        $this->line("<info>Debugging is currently: </info>" . (config('phanda.debug') == true ? "enabled" : "disabled"));
    }

    protected function validateModeArgument($mode)
    {
        if (in_array($mode, $this->allowedTrueModes)) {
            return true;
        }

        if (in_array($mode, $this->allowedFalseModes)) {
            return true;
        }

        $this->warning("Mode argument must be one of: " . implode(', ', array_merge($this->allowedTrueModes, $this->allowedFalseModes)));

        return false;
    }

    protected function enableDebugMode()
    {
        $this->info("Setting application debug mode to be enabled.");
        if ($this->confirmToProceed()) {
            file_put_contents(
                environment_path(phanda()->getAppEnvironmentFile()),
                preg_replace(
                    $this->debugReplacementPattern(),
                    'APPLICATION_ALLOW_DEBUGGING=true',
                    file_get_contents(environment_path(phanda()->getAppEnvironmentFile()))
                )
            );
            $this->line("<info>Application debugging enabled.</info>");
        }
    }

    protected function disableDebugMode()
    {
        $this->info("Setting application debug mode to be disabled.");
        file_put_contents(
            environment_path(phanda()->getAppEnvironmentFile()),
            preg_replace(
                $this->debugReplacementPattern(),
                'APPLICATION_ALLOW_DEBUGGING=false',
                file_get_contents(environment_path(phanda()->getAppEnvironmentFile()))
            )
        );
        $this->line("<info>Application debugging disabled.</info>");
    }

    public function getArguments()
    {
        return [
            ['mode', InputArgument::OPTIONAL, 'Whether to enable or disable debug mode. Value must be either true/false or enable/disable.', null]
        ];
    }

    protected function debugReplacementPattern()
    {
        $escaped = preg_quote('='.(config('phanda.debug') == true ? 'true' : 'false'), '/');
        return "/^APPLICATION_ALLOW_DEBUGGING{$escaped}/m";
    }
}