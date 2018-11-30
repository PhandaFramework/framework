<?php


namespace Phanda\Foundation\Http;

use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Http\Kernel as HttpKernel;
use Phanda\Contracts\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Kernel implements HttpKernel
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Router
     */
    protected $router;

    /**
     * Kernel constructor.
     * @param Application $app
     * @param Router $router
     */
    public function __construct(Application $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handle($request)
    {
        // TODO: Send request through router
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function terminate($request, $response)
    {

    }
}