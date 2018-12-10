<?php

namespace Phanda\Caching;

use Phanda\Contracts\Caching\CacheRepository;
use Phanda\Filesystem\Filesystem;

class FileCacheRepository implements CacheRepository
{

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $basePath;

    public function __construct(Filesystem $filesystem, $basePath = null)
    {
        $this->filesystem = $filesystem;
        $this->basePath = $basePath ?? 'phanda';
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->filesystem->fileExists($this->convertKeyToPath($key));
    }

    /**
     * @param  array|string $key
     * @param  mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if(!$this->has($key)) {
            return $default;
        }

        return $this->filesystem->loadFile($this->convertKeyToPath($key));
    }

    /**
     * @return array
     */
    public function all()
    {

    }

    /**
     * @param  array|string $key
     * @param  mixed $value
     * @return mixed
     */
    public function set($key, $value = null)
    {
        if(is_null($value)) {
            $this->filesystem->remove($this->convertKeyToPath($key));
            return null;
        }

        $this->filesystem->saveContents($this->convertKeyToPath($key), $value);
        return $value;
    }

    /**
     * Checks if a given value is newer than the old.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function outdated($key, $value)
    {
        if($this->get($key) !== $value) {
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function updateIfOutdated($key, $value)
    {
        if($this->outdated($key, $value)) {
            $this->set($key, $value);
        }

        return $value;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function convertKeyToPath($key)
    {
        return storage_path(rtrim($this->basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $key);
    }
}