<?php

namespace Phanda\Foundation\Bootstrap;

use ErrorException;
use Exception;
use Phanda\Contracts\Exceptions\ExceptionHandler;
use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use Phanda\Foundation\Application;
use Phanda\Foundation\Http\Request;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class BootstrapExceptionHandler implements Bootstrap
{
    /**
     * @var Application
     */
    protected $phanda;

    /**
     * @param Application $phanda
     * @return void
     */
    public function bootstrap(Application $phanda)
    {
        $this->phanda = $phanda;

        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        if(!$this->phanda->checkEnvironment('testing')) {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $context
     *
     * @throws ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * @param $e
     */
    public function handleException($e)
    {
        if (! $e instanceof Exception) {
            $e = new FatalThrowableError($e);
        }

        try {
            $this->getPhandaExceptionHandler()->save($e);
        } catch (Exception $e) {
            //
        }

        if ($this->phanda->inConsole()) {
            $this->renderExceptionForKungfu($e);
        } else {
            $this->renderExceptionForHttp($e);
        }
    }

    /**
     * Handles the shutdown, reports error if any
     */
    public function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }

    /**
     * Create a new fatal exception instance from an error array.
     *
     * @param  array $error
     * @param  int|null $traceOffset
     * @return FatalErrorException
     */
    protected function fatalExceptionFromError(array $error, $traceOffset = null)
    {
        return new FatalErrorException(
            $error['message'], $error['type'], 0, $error['file'], $error['line'], $traceOffset
        );
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int  $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }

    /**
     * @param Exception $e
     */
    protected function renderExceptionForKungfu(Exception $e)
    {
        $this->getPhandaExceptionHandler()->outputToKungfu(new ConsoleOutput(), $e);
    }

    /**
     * @param Exception $e
     */
    protected function renderExceptionForHttp(Exception $e)
    {
        /** @var Request $request */
        $request = $this->phanda['request'];
        $this->getPhandaExceptionHandler()->render($request, $e)->send();
    }

    /**
     * @return ExceptionHandler
     */
    protected function getPhandaExceptionHandler()
    {
        return $this->phanda->create(ExceptionHandler::class);
    }
}