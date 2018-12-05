<?php

namespace Phanda\Exceptions;

use Exception;
use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Exceptions\ExceptionHandler as ExceptionHandlerContract;
use Phanda\Foundation\Http\Request;
use Phanda\Foundation\Http\Response;
use Phanda\Support\PhandArr;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application as BaseConsoleApplication;

class ExceptionHandler implements ExceptionHandlerContract
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $ignoredExceptions = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Exception $e
     * @return bool
     */
    public function shouldSave(Exception $e)
    {
        return !$this->shouldntSave($e);
    }

    /**
     * @param Exception $e
     * @return bool
     */
    public function shouldntSave(Exception $e)
    {
        return is_null(PhandArr::first($this->ignoredExceptions, function($type) use ($e) {
            return $e instanceof $type;
        }));
    }

    /**
     * @param Exception $e
     * @return mixed
     */
    public function save(Exception $e)
    {
        if($this->shouldntSave($e)) {
            return;
        }


    }

    /**
     * @param Request $request
     * @param Exception $e
     * @return Response
     */
    public function render(Request $request, Exception $e)
    {

    }

    /**
     * @param OutputInterface $output
     * @param Exception $e
     * @return void
     */
    public function outputToKungfu(OutputInterface $output, Exception $e)
    {
        (new BaseConsoleApplication())->renderException($e, $output);
    }

    /**
     * @param Exception $e
     * @return $this
     */
    public function ignoreException(Exception $e)
    {
        $this->ignoredExceptions[] = $e;
        return $this;
    }

    /**
     * @param array $e
     * @return $this
     */
    public function ignoreExceptions(array $e)
    {
        $this->ignoredExceptions = array_merge($this->ignoredExceptions, $e);
        return $this;
    }
}