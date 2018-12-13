<?php

namespace Phanda\Providers\Scene\Bamboo;

use Phanda\Configuration\Repository;
use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Scene\Factory;
use Phanda\Filesystem\Filesystem;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Scene\Bamboo\Compiler as BambooCompiler;
use Phanda\Scene\Engine\SceneCompilerEngine;

// TODO: Fix this and break away from scene service provider.
class BambooServiceProvider extends AbstractServiceProvider
{
    /**
     * Registers the bamboo compiler, and its aliases
     */
    public function register()
    {
        $this->registerBamboo();
        $this->registerBambooExtension();
    }

    /**
     * Registers the bamboo compiler
     */
    protected function registerBamboo()
    {
        $this->phanda->singleton('bamboo.compiler', function ($phanda) {
            /** @var Application $phanda */
            $filesystem = $phanda->create(Filesystem::class);
            /** @var Repository $config */
            $config = $phanda->create(Repository::class);
            $cachedPath = $config->get('scene.cachedPath');
            $bambooCompiler = new BambooCompiler(
                $filesystem,
                $cachedPath
            );

            return $bambooCompiler;
        });

        $this->phanda->alias('bamboo.compiler', BambooCompiler::class);
    }

    /**
     * Registers the .bamboo.php extension
     */
    protected function registerBambooExtension()
    {
        /** @var Factory $sceneFactory */
        $sceneFactory = $this->phanda->create(Factory::class);
        $sceneFactory->addExtension('bamboo.php', 'bamboo', function() {
            return new SceneCompilerEngine(
                $this->phanda->create(BambooCompiler::class)
            );
        });
    }
}