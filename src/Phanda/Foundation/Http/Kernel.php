<?php


namespace Phanda\Foundation\Http;

use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Http\Kernel as HttpKernel;
use Phanda\Contracts\Routing\Router;

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
        try {
            $request->enableHttpMethodParameterOverride();

            $response = $this->sendRequestToRouter($request);
        } catch(\Exception $e) {
            $this->saveException($e);

            $response = $this->renderException($request, $e);
        } catch(\Throwable $e) {
            $this->saveException($e);

            $response = $this->renderException($request, $e);
        }

        return $response;
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

    /**
     * @param Request $request
     * @return Response
     */
    protected function sendRequestToRouter(Request $request)
    {

    }

    /**
     * @param \Exception $e
     */
    protected function saveException(\Exception $e)
    {

    }

    /**
     * @param Request $request
     * @param \Exception $e
     * @return Response
     */
    protected function renderException(Request $request, \Exception $e)
    {

    }
}