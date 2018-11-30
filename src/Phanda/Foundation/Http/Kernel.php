<?php


namespace Phanda\Foundation\Http;

use Phanda\Contracts\Http\Kernel as HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Kernel implements HttpKernel
{
    /**
     * @param Request $request
     * @return Response
     */
    public function handle($request)
    {
        // TODO: Implement handle() method.
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function terminate($request, $response)
    {
        // TODO: Implement terminate() method.
    }
}