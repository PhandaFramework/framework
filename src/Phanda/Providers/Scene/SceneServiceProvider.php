<?php

namespace Phanda\Providers\Scene;

use Phanda\Configuration\Repository;
use Phanda\Contracts\Events\Dispatcher;
use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Scene\Factory as FactoryContract;
use Phanda\Contracts\Support\Scene\SceneFinder;
use Phanda\Filesystem\Filesystem;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Providers\Scene\Bamboo\BambooServiceProvider;
use Phanda\Scene\Engine\EngineResolver;
use Phanda\Scene\Engine\FileEngine;
use Phanda\Scene\Engine\PhpEngine;
use Phanda\Scene\Engine\SceneCompilerEngine;
use Phanda\Scene\Factory;
use Phanda\Scene\Bamboo\Compiler as BambooCompiler;


class SceneServiceProvider extends AbstractServiceProvider
{
    /**
     * Register the basic providers needed to return scenes.
     */
    public function register()
    {
        $this->registerFactory();
        $this->registerSceneFinder();
        $this->registerEngineResolver();
    }

    /**
     * Registers the factory, and it's aliases
     */
    protected function registerFactory()
    {
        $this->phanda->singleton('scene', function ($phanda) {
            /** @var Application $phanda */
            $resolver = $phanda->create(EngineResolver::class);
            $finder = $phanda->create(SceneFinder::class);
            $eventDispatcher = $phanda->create(Dispatcher::class);

            $factory = new Factory($resolver, $finder, $eventDispatcher);
            $factory->setContainer($phanda);
            $factory->share('phanda', $phanda);

            $this->registerBaseExtensions($factory);

            return $factory;
        });

        $this->phanda->alias('scene', FactoryContract::class);
        $this->phanda->alias('scene', Factory::class);
    }

    /**
     * Registers the scene finder, and its aliases
     */
    protected function registerSceneFinder()
    {
        $this->phanda->attach('scene.finder', function ($phanda) {
            /** @var Application $phanda */
            $filesystem = $phanda->create(Filesystem::class);
            /** @var Repository $config */
            $config = $phanda->create(Repository::class);
            $paths = $config->get('scene.paths');

            return new \Phanda\Support\Scene\SceneFinder(
                $filesystem,
                $paths
            );
        });

        $this->phanda->alias('scene.finder', SceneFinder::class);
        $this->phanda->alias('scene.finder', \Phanda\Support\Scene\SceneFinder::class);
    }

    /**
     * Registers the engine resolver and it's aliases.
     */
    protected function registerEngineResolver()
    {
        $this->phanda->singleton('scene.engine.resolver', function () {
            return new EngineResolver();
        });

        $this->phanda->alias('scene.engine.resolver', EngineResolver::class);
    }

    /**
     * Registers the base extensions of phanda.
     *
     * @param FactoryContract $factory
     */
    protected function registerBaseExtensions(FactoryContract $factory)
    {
        $this->registerCssEngine($factory);
        $this->registerPhpExtension($factory);
    }

    /**
     * Registers the '.css' extension.
     *
     * @param FactoryContract $factory
     */
    protected function registerCssEngine(FactoryContract $factory)
    {
        $factory->addExtension('css', 'file', function () {
            return new FileEngine();
        });
    }

    /**
     * Registers the '.php' extension.
     *
     * @param FactoryContract $factory
     */
    protected function registerPhpExtension(FactoryContract $factory)
    {
        $factory->addExtension('php', 'php', function () {
            return new PhpEngine();
        });
    }
}