<?php

namespace Phanda\Scene\Engine;

use ErrorException;
use Exception;
use Phanda\Contracts\Scene\Compiler\Compiler;

class SceneCompilerEngine extends PhpEngine
{

    /**
     * The Scene compiler instance.
     *
     * @var Compiler
     */
    protected $compiler;

    /**
     * A stack of the last compiled templates.
     *
     * @var array
     */
    protected $lastCompiled = [];

    /**
     * Create a new Scene engine instance.
     *
     * @param  Compiler  $compiler
     * @return void
     */
    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * @param string $path
     * @param array $data
     * @return string
     *
     * @throws \Exception
     */
    public function get($path, array $data = [])
    {
        $this->lastCompiled[] = $path;

        if($this->compiler->isSceneOutdated($path)) {
            $this->compiler->compileScene($path);
        }

        $compiledScenePath = $this->compiler->getPathToCompiledScene($path);
        $compiledScene = $this->evaluateScenePath($compiledScenePath, $data);

        array_pop($this->lastCompiled);
        $this->setLastRenderedScene($compiledScene);
        return $compiledScene;
    }

    /**
     * Handle a scene exception.
     *
     * @param  \Exception  $e
     * @param  int  $obLevel
     * @return void
     *
     * @throws \Exception
     */
    protected function handleSceneException(Exception $e, $obLevel)
    {
        $e = new ErrorException($this->getMessage($e), 0, 1, $e->getFile(), $e->getLine(), $e);
        parent::handleSceneException($e, $obLevel);
    }

    /**
     * Get the exception message for an exception.
     *
     * @param  \Exception  $e
     * @return string
     */
    protected function getMessage(Exception $e)
    {
        return $e->getMessage().' (Scene: '.realpath(end($this->lastCompiled)).')';
    }

    /**
     * Get the compiler implementation.
     *
     * @return Compiler
     */
    public function getCompiler()
    {
        return $this->compiler;
    }

}