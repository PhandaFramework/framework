<?php


namespace Phanda\Foundation\Http;

use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use Phanda\Contracts\Http\Kernel as HttpKernel;
use Phanda\Contracts\Routing\Router;
use Phanda\Foundation\Bootstrap\BootstrapConfig;
use Phanda\Foundation\Bootstrap\BootstrapEnvironment;
use Phanda\Foundation\Bootstrap\BootstrapPhanda;
use Phanda\Foundation\Bootstrap\BootstrapProviders;

class Kernel implements HttpKernel
{
    /**
     * @var Application
     */
    protected $phanda;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Bootstrap[]
     */
    protected $httpBootstrappers = [
        BootstrapEnvironment::class,
        BootstrapConfig::class,
        BootstrapProviders::class,
        BootstrapPhanda::class
    ];

    /**
     * Kernel constructor.
     * @param Application $phanda
     * @param Router $router
     */
    public function __construct(Application $phanda, Router $router)
    {
        $this->phanda = $phanda;
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

    /**
     * Bootstraps the HTTP Kernel
     */
    public function bootstrap()
    {
        if(!$this->phanda->hasBeenBootstrapped()) {
            $this->phanda->bootstrapWith($this->bootstrappers());
        }
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        return $this->httpBootstrappers;
    }
}