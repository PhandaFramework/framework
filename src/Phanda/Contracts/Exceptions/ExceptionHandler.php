<?php

namespace Phanda\Contracts\Exceptions;

use Exception;
use Phanda\Foundation\Http\Request;
use Phanda\Foundation\Http\Response;
use Symfony\Component\Console\Output\OutputInterface;

interface ExceptionHandler
{
    /**
     * @param Exception $e
     * @return mixed
     */
    public function save(Exception $e);

    /**
     * @param Request $request
     * @param Exception $e
     * @return Response
     */
    public function render(Request $request, Exception $e);

    /**
     * @param OutputInterface $output
     * @param Exception $e
     * @return void
     */
    public function outputToKungfu(OutputInterface $output, Exception $e);

    /**
     * @param Exception $e
     * @return bool
     */
    public function shouldSave(Exception $e);

    /**
     * @param Exception $e
     * @return $this
     */
    public function ignoreException(Exception $e);

    /**
     * @param array $e
     * @return $this
     */
    public function ignoreExceptions(array $e);
}