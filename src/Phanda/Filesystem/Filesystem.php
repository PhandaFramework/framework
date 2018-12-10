<?php

namespace Phanda\Filesystem;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class Filesystem extends SymfonyFilesystem
{
    /**
     * Returns the time last modified
     *
     * @param $path
     * @return bool|int
     */
    public function lastModified($path)
    {
        return filemtime($path);
    }

    /**
     * @param $path
     * @return string
     *
     * @throws FileNotFoundException
     */
    public function loadFile($path)
    {
        if(is_file($path)) {
            return file_get_contents($path);
        }

        throw new FileNotFoundException("File does not exists at {$path}");
    }

    /**
     * @param $path
     * @param $contents
     * @return bool|int
     */
    public function saveContents($path, $contents)
    {
        return file_put_contents($path, $contents, 0);
    }

    /**
     * @param $path
     * @return bool
     */
    public function fileExists($path)
    {
        return is_file($path);
    }
}