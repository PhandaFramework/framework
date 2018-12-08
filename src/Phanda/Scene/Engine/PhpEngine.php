<?php

namespace Phanda\Scene\Engine;

use Exception;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class PhpEngine extends AbstractEngine
{

    /**
     * Get the evaluated contents of the scene.
     *
     * @param  string $path
     * @param  array $data
     * @return string
     *
     * @throws Exception
     */
    public function get($path, array $data = [])
    {
        $scene = $this->evaluateScenePath($path, $data);
        $this->setLastRenderedScene($scene);
        return $scene;
    }

    /**
     * @param $path
     * @param $data
     * @return string
     *
     * @throws Exception
     */
    protected function evaluateScenePath($path, $data)
    {
        $obLevel = ob_get_level();
        ob_start();
        extract($data, EXTR_SKIP);

        try {
            include $path;
        } catch (Exception $e) {
            $this->handleSceneException($e, $obLevel);
        } catch (Throwable $e) {
            $this->handleSceneException(new FatalThrowableError($e), $obLevel);
        }

        return ltrim(ob_get_clean());
    }

    /**
     * @param Exception $e
     * @param $obLevel
     *
     * @throws Exception
     */
    protected function handleSceneException(Exception $e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $e;
    }
}