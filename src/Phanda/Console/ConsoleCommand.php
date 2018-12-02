<?php

namespace Phanda\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleCommand extends SymfonyCommand
{

    /**
     * @var \Phanda\Contracts\Foundation\Application
     */
    protected $phanda;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var PhandaOutputStyle
     */
    protected $output;

    /**
     * @var string
     */
    protected $signature;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * The default verbosity of output commands.
     *
     * @var int
     */
    protected $verbosity = OutputInterface::VERBOSITY_NORMAL;

    /**
     * The mapping between human readable verbosity levels and Symfony's OutputInterface.
     *
     * @var array
     */
    protected $verbosityMap = [
        'v' => OutputInterface::VERBOSITY_VERBOSE,
        'vv' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv' => OutputInterface::VERBOSITY_DEBUG,
        'quiet' => OutputInterface::VERBOSITY_QUIET,
        'normal' => OutputInterface::VERBOSITY_NORMAL,
    ];

    public function __construct()
    {
        if (isset($this->signature)) {
            $this->configurePhandaCommand();
        } else {
            parent::__construct($this->name);
        }

        $this->setDescription($this->description);
        $this->setHidden($this->isHidden());

        if (! isset($this->signature)) {
            $this->specifyParameters();
        }

        parent::__construct();
    }

    protected function configurePhandaCommand()
    {

    }

}