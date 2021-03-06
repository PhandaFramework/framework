<?php


namespace Phanda\Foundation\Http;

use Exception;
use Phanda\Conduit\HttpConduit;
use Phanda\Contracts\Exceptions\ExceptionHandler;
use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Http\Kernel as HttpKernel;
use Phanda\Contracts\Routing\Router;
use Phanda\Foundation\Bootstrap\BootstrapConfig;
use Phanda\Foundation\Bootstrap\BootstrapEnvironment;
use Phanda\Foundation\Bootstrap\BootstrapExceptionHandler;
use Phanda\Foundation\Bootstrap\BootstrapFacades;
use Phanda\Foundation\Bootstrap\BootstrapPhanda;
use Phanda\Foundation\Bootstrap\BootstrapProviders;
use Symfony\Component\Debug\Exception\FatalThrowableError;

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
     * @var array
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
    protected function saveException($e)
    {
        if (! $e instanceof Exception) {
            $e = new FatalThrowableError($e);
        }

        /** @var ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->phanda->create(ExceptionHandler::class);
        $exceptionHandler->save($e);
    }

    /**
     * @param Request $request
     * @param \Exception $e
     * @return Response
     */
    protected function renderException(Request $request, $e)
    {
		if (! $e instanceof Exception) {
			$e = new FatalThrowableError($e);
		}

        /** @var ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->phanda->create(ExceptionHandler::class);
        return $exceptionHandler->render($request, $e);
    }

    /**
     * Bootstraps the HTTP Kernel
     */
    public function bootstrap()
    {
        if(!$this->phanda->hasBeenBootstrapped()) {
            $this->phanda->bootstrapWith($this->getBootstrappers());
        }
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    public function getBootstrappers()
    {
        return $this->httpBootstrappers;
    }

    /**
     * Adds a bootstrapper to the http kernel
     *
     * @param string $bootstrapper
     * @return $this
     */
    public function addBootstrapper($bootstrapper) {
        $this->httpBootstrappers[] = $bootstrapper;
        return $this;
    }

    /**
     * Advanced use only: Sets the http bootstrappers
     *
     * @param array $bootstrappers
     * @return $this
     */
    public function setBootstrappers(array $bootstrappers) {
        $this->httpBootstrappers = $bootstrappers;
        return $this;
    }

    /**
     * Advanced use only: Clears the http bootstrappers
     *
     * @return $this
     */
    public function clearBootstrappers()
    {
        $this->httpBootstrappers = [];
        return $this;
    }
}