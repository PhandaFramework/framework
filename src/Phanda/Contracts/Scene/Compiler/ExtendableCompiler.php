<?php

namespace Phanda\Contracts\Scene\Compiler;

interface ExtendableCompiler extends Compiler
{

    /**
     * Register a custom extension compiler.
     *
     * @param  callable  $extensionCompiler
     * @return $this
     */
    public function extend(callable $extensionCompiler);

    /**
     * Get the extensions used by the compiler.
     *
     * @return array
     */
    public function getExtensions();

}