<?php

namespace Phanda\Providers\Caching;

use Phanda\Caching\FileCacheRepository;
use Phanda\Contracts\Caching\CacheRepository;
use Phanda\Contracts\Foundation\Application;
use Phanda\Filesystem\Filesystem;
use Phanda\Providers\AbstractServiceProvider;

class CachingServiceProvider extends AbstractServiceProvider
{
    /**
     * Initialises core routing
     */
    public function register()
    {
        $this->registerCacheRepository();
    }

    /**
     * Registers the cache repository and its aliases
     */
    protected function registerCacheRepository()
    {
        $driver = trim(strtolower(config('cache.driver', 'file')));

        $this->phanda->singleton('cache', function($phanda) use($driver) {
           /** @var Application $phanda */
            switch($driver) {
                default:
                case "file":
                    /** @var Filesystem $filesystem */
                    $filesystem = $phanda->create(Filesystem::class);
                    return new FileCacheRepository($filesystem, config('cache.file_base_path', 'phanda'));
            }
        });

        $this->phanda->alias('cache', CacheRepository::class);
    }
}