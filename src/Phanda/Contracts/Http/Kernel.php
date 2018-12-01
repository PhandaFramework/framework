<?php


namespace Phanda\Contracts\Http;

use Phanda\Foundation\Http\Request;
use Phanda\Foundation\Http\Response;

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