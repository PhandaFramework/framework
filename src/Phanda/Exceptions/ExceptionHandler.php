<?php

namespace Phanda\Exceptions;

use Exception;
use Phanda\Support\Facades\Scene\Scene;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Whoops\Run as Whoops;
use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Exceptions\ExceptionHandler as ExceptionHandlerContract;
use Phanda\Contracts\Support\Responsable;
use Phanda\Exceptions\Foundation\Http\HttpResponseException;
use Phanda\Exceptions\Handlers\WhoopsHandler;
use Phanda\Foundation\Http\Request;
use Phanda\Foundation\Http\Response;
use Phanda\Support\PhandArr;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application as BaseConsoleApplication;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

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
        return !is_null(PhandArr::first($this->ignoredExceptions, function ($type) use ($e) {
            return $e instanceof $type;
        }));
    }

    /**
     * @param Exception $e
     * @return mixed
     */
    public function save(Exception $e)
    {
        if ($this->shouldntSave($e)) {
            dd($this->ignoredExceptions);
            return;
        }

        if (method_exists($e, 'save')) {
            $e->save();
        }

        return null;
    }

    /**
     * @param Request $request
     * @param Exception $e
     * @return Response
     */
    public function render(Request $request, Exception $e)
    {
        if ($e instanceof Responsable) {
            $e->toResponse($request);
        }

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        return $this->prepareResponse($request, $e);
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

    protected function prepareResponse(Request $request, Exception $e)
    {
        if (!$this->isHttpException($e) && config('phanda.debug')) {
            /** @var HttpException $e */
            return $this->toPhandaResponse(
                $this->convertExceptionToRenderableException($e),
                $e
            );
        }

        if (!$this->isHttpException($e)) {
            $e = new HttpException(500, $e->getMessage());
        }

        return $this->toPhandaResponse(
            $this->renderHttpException($e),
            $e
        );
    }

    /**
     * Determine if the given exception is an HTTP exception.
     *
     * @param  \Exception $e
     * @return bool
     */
    protected function isHttpException(Exception $e)
    {
        return $e instanceof HttpExceptionInterface;
    }

    /**
     * @param Response $response
     * @param Exception $e
     * @return Response
     */
    protected function toPhandaResponse(Response $response, Exception $e)
    {
        return $response->setException($e);
    }

    /**
     * @param HttpException $e
     * @return Response
     */
    protected function convertExceptionToRenderableException(Exception $e)
    {
        return Response::create(
            $this->renderExceptionContent($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : []
        );
    }

    /**
     * @param Exception $e
     * @return string
     */
    protected function renderExceptionContent(Exception $e)
    {
        try {
            return config('phanda.debug') ?
                $this->renderUsingWhoops($e) :
                $this->renderUsingSymfony($e, config('phanda.debug'));
        } catch (Exception $e) {
            return $this->renderUsingSymfony($e, config('phanda.debug'));
        }
    }

    /**
     * @param Exception $e
     * @param $debug
     * @return string
     */
    protected function renderUsingSymfony(Exception $e, $debug)
    {
        return (new SymfonyExceptionHandler($debug))->getHtml(
            FlattenException::create($e)
        );
    }

    /**
     * @param Exception $e
     * @return string
     */
    protected function renderUsingWhoops(Exception $e)
    {
        /** @var Whoops $whoops */
        $whoops = modify(new Whoops(), function ($whoops) {
            /** @var Whoops $whoops */
            $whoops->pushHandler($this->whoopsHandler());
            $whoops->writeToOutput(false);
            $whoops->allowQuit(false);
        });

        return $whoops->handleException($e);
    }

    /**
     * @return \Whoops\Handler\PrettyPageHandler
     */
    protected function whoopsHandler()
    {
        return (new WhoopsHandler())->forDebug();
    }

    /**
     * @param HttpException $e
     * @return Response
     */
    protected function renderHttpException(HttpException $e)
    {
        $this->registerExceptionScenes();
        $scene = "errors::{$e->getStatusCode()}";

        if(scene()->exists($scene)) {
            return responseManager()->createSceneResponse(
                $scene,
                [
                    'errors' => [$e->getMessage()],
                    'exception' => $e
                ],
                $e->getStatusCode(),
                $e->getHeaders()
            );
        }

        return $this->convertExceptionToRenderableException($e);
    }

    /**
     * Registers the application exception scenes for the given errors.
     */
    protected function registerExceptionScenes()
    {
        $paths = createDictionary(
            config('scene.error_scenes_path', assets_path('scenes/error'))
        )->push(__DIR__ . DIRECTORY_SEPARATOR . 'exception_scenes')->all();

        scene()->replaceNamespace(
            'errors',
            $paths
        );
    }
}