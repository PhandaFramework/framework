<?php


namespace Phanda\Contracts\Http;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface Kernel
{

    /**
     * @param Request $request
     * @return Response
     */
    public function handle($request);

    /**
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function terminate($request, $response);

}