<?php

namespace Phanda\Exceptions\Handlers;

use Phanda\Support\PhandArr;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Whoops\Handler\PrettyPageHandler;

class WhoopsHandler
{

    /**
     * Create a new Whoops handler for debug mode.
     *
     * @return \Whoops\Handler\PrettyPageHandler
     */
    public function forDebug()
    {
        return modify(new PrettyPageHandler(), function($handler) {
            /** @var PrettyPageHandler $handler */
            $handler->handleUnconditionally(true);
            $this->registerProjectPaths($handler);
        });
    }

    /**
     * Registers all paths in the project, except for the vendor directory.
     *
     * @param PrettyPageHandler $handler
     */
    protected function registerProjectPaths(PrettyPageHandler $handler)
    {
        $directories = [];

        foreach(Finder::create()->in(base_path())->directories()->depth(0)->sortByName() as $directory) {
            /** @var SplFileInfo $directory */
            $directories[] = $directory->getPathname();
        }

        $handler->setApplicationPaths(PhandArr::except(
            $directories,
            [base_path('vendor')]
        ));
    }

}