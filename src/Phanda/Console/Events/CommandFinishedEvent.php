<?php

namespace Phanda\Console\Events;

use Phanda\Events\Event;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandFinishedEvent extends Event
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
     * @var int
     */
    private $exitCode;

    /**
     * CommandFinishedEvent constructor.
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param int $exitCode
     */
    public function __construct($command, InputInterface $input, OutputInterface $output, $exitCode)
    {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;
        $this->exitCode = $exitCode;
    }
}