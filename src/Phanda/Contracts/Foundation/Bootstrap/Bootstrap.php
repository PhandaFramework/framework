<?php

namespace Phanda\Contracts\Foundation\Bootstrap;

use Phanda\Foundation\Application;

interface Bootstrap
{

    /**
     * @param Application $phanda
     * @return void
     */
    public function bootstrap(Application $phanda);

}