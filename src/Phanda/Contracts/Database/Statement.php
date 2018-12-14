<?php

namespace Phanda\Contracts\Database;

interface Statement
{

    /**
     * Executes the given statement.
     *
     * @return bool
     */
    public function execute(): bool;

}