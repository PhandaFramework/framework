<?php


namespace Phanda\Contracts\Console;


interface Kernel
{

    public function handle();

    public function terminate();

}