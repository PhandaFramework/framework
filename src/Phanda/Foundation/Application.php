<?php

namespace Phanda\Foundation;

use Phanda\Container\Container;
use Phanda\Contracts\Foundation\Application as ApplicationContract;
use Phanda\Foundation\Http\Response;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Providers\Events\EventServiceProvider;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Application extends Container implements ApplicationContract, HttpKernelInterface
{
    const VERSION = '0.0.0';

    /**
     * @var string
     */
    protected $appPath;

    /**
     * @var bool
     */
    protected $hasBootstrapped = false;

    /**
     * @var bool
     */
    protected $started = false;

    /**
     * @var array
     */
    protected $startingCallbacks = [];

    /**
     * @var array
     */
    protected $startedCallbacks = [];

    /**
     * @var array
     */
    protected $stoppingCallbacks = [];

    /**
     * @var stirng
     */
    protected $storagePath;

    /**
     * @var string
     */
    protected $environmentFile = '.env';

    /**
     * @var string
     */
    protected $namespace;

    /**
     * Application constructor.
     *
     * @param string|null $appPath
     * @throws \Phanda\Exceptions\Container\ResolvingAttachmentException
     * @throws \ReflectionException
     */
    public function __construct($appPath = null)
    {
        if($appPath !== null) {
            $this->setAppPath($appPath);
        }

        $this->registerPhandaAttachments();
        $this->registerPhandaServiceProviders();
    }

    /**
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * @throws \Phanda\Exceptions\Container\ResolvingAttachmentException
     * @throws \ReflectionException
     */
    protected function registerPhandaAttachments()
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(Container::class, $this);
    }

    protected function registerPhandaServiceProviders()
    {
        $this->register(new EventServiceProvider($this));
    }

    /**
     * @return string
     */
    public function appPath()
    {
        // TODO: Implement basePath() method.
    }

    /**
     * @return string
     */
    public function environment()
    {
        // TODO: Implement environment() method.
    }

    /**
     * @return bool
     */
    public function inConsole()
    {
        // TODO: Implement inConsole() method.
    }

    /**
     * @return bool
     */
    public function isDownForMaintenance()
    {
        // TODO: Implement isDownForMaintenance() method.
    }

    /**
     * @return void
     */
    public function start()
    {
        // TODO: Implement start() method.
    }

    /**
     * @param mixed $callback
     * @return void
     */
    public function starting($callback)
    {
        // TODO: Implement starting() method.
    }

    /**
     * @param mixed $callback
     * @return void
     */
    public function started($callback)
    {
        // TODO: Implement started() method.
    }

    /**
     * @param SymfonyRequest $request
     * @param int $type
     * @param bool $catch
     * @return Response|void
     */
    public function handle(SymfonyRequest $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        // TODO: Implement handle() method.
    }

    /**
     * @param string|AbstractServiceProvider $provider
     * @param bool $force
     * @return AbstractServiceProvider
     */
    public function register($provider, $force = false)
    {
        // TODO: Implement register() method.
    }
}