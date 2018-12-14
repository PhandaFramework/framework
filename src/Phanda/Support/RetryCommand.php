<?php

namespace Phanda\Support;

use Phanda\Contracts\Support\RetryCommandStrategy;

class RetryCommand
{
    /**
     * @var RetryCommandStrategy
     */
    protected $commandStrategy;

    /**
     * @var int
     */
    protected $maximumAttempts;

    /**
     * RetryCommand constructor.
     * @param RetryCommandStrategy $commandStrategy
     * @param int $maximumAttempts
     */
    public function __construct(RetryCommandStrategy $commandStrategy, $maximumAttempts = 1)
    {
        $this->commandStrategy = $commandStrategy;
        $this->maximumAttempts = $maximumAttempts;
    }

    /**
     * Runs a command, and continues to try to execute the command if an exception
     * is caught for the maximum amount of times specified.
     *
     * @param callable $action
     * @return mixed
     * @throws \Exception
     */
    public function run(callable $action)
    {
        $retryCount = 0;
        $lastException = null;

        do {
            try {
                return $action();
            } catch (\Exception $e) {
                $lastException = $e;
                if(!$this->commandStrategy->shouldRetry($e)) {
                    throw $e;
                }
            }
        } while($this->maximumAttempts > $retryCount++);

        if($lastException !== null) {
            throw $lastException;
        }
    }

}