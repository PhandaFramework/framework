<?php

namespace Phanda\Scene\Compiler;

use Phanda\Contracts\Scene\Compiler\Compiler as CompilerContract;
use Phanda\Filesystem\Filesystem;

abstract class AbstractCompiler implements CompilerContract
{
    /**
     * @var string
     */
    protected $compiledSceneDir;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Compiler constructor.
     *
     * @param Filesystem $filesystem
     * @param string $compiledSceneDir
     */
    public function __construct(Filesystem $filesystem, $compiledSceneDir)
    {
        $this->filesystem = $filesystem;
        $this->compiledSceneDir = $compiledSceneDir;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getPathToCompiledScene($path)
    {
        return $this->compiledSceneDir . "/" . sha1($path) . ".php";
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isSceneOutdated($path)
    {
        $compiledScene = $this->getPathToCompiledScene($path);

        if(!$this->filesystem->exists($compiledScene)) {
            return false;
        }

        return $this->filesystem->lastModified($path) >= $this->filesystem->lastModified($compiledScene);
    }
}