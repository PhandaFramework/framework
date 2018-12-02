<?php

namespace Phanda\Console\Events;

use Phanda\Events\Event;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandStartingEvent extends Event
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * CommandStartingEvent constructor.
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct($command, InputInterface $input, OutputInterface $output)
    {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;
    }

}