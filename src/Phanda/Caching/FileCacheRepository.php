<?php

namespace Phanda\Caching;

use Phanda\Contracts\Caching\CacheRepository;
use Phanda\Filesystem\Filesystem;
use Phanda\Support\PhandArr;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
        $files = [];
        $cachePath = $this->convertKeyToPath();

        foreach(Finder::create()->files()->in($cachePath) as $file)
        {
            /** @var SplFileInfo $file */
            $directory = $this->getNestedDirectory($file, $cachePath);
            PhandArr::set($files, $directory.basename($file->getRealPath(), ".".$file->getExtension()), $file->getRealPath());
        }

        ksort($files, SORT_NATURAL);
        return $files;
    }

    /**
     * @param SplFileInfo $file
     * @param string $configPath
     * @return string
     */
    protected function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }

        return $nested;
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
    protected function convertKeyToPath($key = null)
    {
        return realpath(storage_path(!is_null($key) ? rtrim($this->basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $key : $this->basePath));
    }
}