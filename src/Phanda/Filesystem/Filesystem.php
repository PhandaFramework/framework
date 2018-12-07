<?php

namespace Phanda\Filesystem;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class Filesystem extends SymfonyFilesystem
{
    public function lastModified($path)
    {
        return filemtime($path);
    }
}