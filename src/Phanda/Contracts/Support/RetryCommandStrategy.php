<?php

namespace Phanda\Contracts\Support;

use Exception;

interface RetryCommandStrategy
{

    /**
     * Returns whether a given command should be retried or not
     *
     * @param Exception $exception
     * @return bool
     */
    public function shouldRetry(Exception $exception): bool;

}