<?php

namespace Phanda\Contracts\Scene\Compiler;

interface Compiler
{

    /**
     * @var string $path
     * @return void
     */
    public function compileScene($path);

    /**
     * @var string $path
     * @return bool
     */
    public function isSceneOutdated($path);

    /**
     * @var string $path
     * @return string
     */
    public function getPathToCompiledScene($path);

}