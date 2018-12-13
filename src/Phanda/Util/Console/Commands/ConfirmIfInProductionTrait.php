<?php

namespace Phanda\Util\Console\Commands;

use Phanda\Console\ConsoleCommand;

/**
 * Trait ConfirmIfInProductionTrait
 * @package Phanda\Util\Console\Commands
 *
 * @mixin ConsoleCommand
 */
trait ConfirmIfInProductionTrait
{
    public function confirmToProceed($warning = "Application is in production!")
    {
        if(config('phanda.environment') === 'production') {
            if($this->hasOption('force') && $this->getOption('force')) {
                return true;
            }

            $this->alert($warning);
            $confirmed = $this->confirm("Do you wish to proceed?");

            if(!$confirmed) {
                $this->comment("Cancelled command execution.");
                return false;
            }
        }

        return true;
    }
}