<?php


namespace Phanda\Foundation\Http;

use Phanda\Conduit\HttpConduit;
use Phanda\Contracts\Exceptions\ExceptionHandler;
use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use Phanda\Contracts\Http\Kernel as HttpKernel;
use Phanda\Contracts\Routing\Router;
use Phanda\Foundation\Bootstrap\BootstrapConfig;
use Phanda\Foundation\Bootstrap\BootstrapEnvironment;
use Phanda\Foundation\Bootstrap\BootstrapExceptionHandler;
use Phanda\Foundation\Bootstrap\BootstrapFacades;
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
        BootstrapExceptionHandler::class,
        BootstrapProviders::class,
        BootstrapFacades::class,
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
        $this->phanda->instance('request', $request);
        $this->bootstrap();
        return (new HttpConduit($this->phanda))
            ->send($request)
            ->then($this->dispatchToRouter());
    }

    /**
     * Get the route dispatcher callback.
     *
     * @return \Closure
     */
    protected function dispatchToRouter()
    {
        return function ($request) {
            $this->phanda->instance('request', $request);
            return $this->router->dispatch($request);
        };
    }

    /**
     * @param \Exception $e
     */
    protected function saveException(\Exception $e)
    {
        /** @var ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->phanda[ExceptionHandler::class];
        $exceptionHandler->save($e);
    }

    /**
     * @param Request $request
     * @param \Exception $e
     * @return Response
     */
    protected function renderException(Request $request, \Exception $e)
    {
        /** @var ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->phanda[ExceptionHandler::class];
        return $exceptionHandler->render($request, $e);
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