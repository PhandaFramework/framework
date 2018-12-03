<?php

namespace Phanda\Foundation;

use Closure;
use Phanda\Configuration\Repository as ConfigurationRepository;
use Phanda\Container\Container;
use Phanda\Contracts\Foundation\Application as ApplicationContract;
use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use Phanda\Contracts\Http\Kernel as HttpKernelContract;
use Phanda\Events\Dispatcher;
use Phanda\Foundation\Http\Request;
use Phanda\Foundation\Http\Response;
use Phanda\Providers\AbstractServiceProvider;
use Phanda\Providers\Events\EventServiceProvider;
use Phanda\Providers\ServiceProviderRepository;
use Phanda\Support\Foundation\DiscoverEnvironment;
use Phanda\Support\PhandArr;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Application extends Container implements ApplicationContract, HttpKernelInterface
{
    const VERSION = '0.0.0';

    /**
     * @var string
     */
    protected $basePath;

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
     * All of the registered service providers.
     *
     * @var array
     */
    protected $serviceProviders = [];

    /**
     * All of the loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = [];

    /**
     * @var string
     */
    protected $appPath;

    /**
     * @var string
     */
    protected $assetsPath;

    /**
     * @var string
     */
    protected $bootstrapPath;

    /**
     * @var string
     */
    protected $configPath;

    /**
     * @var string
     */
    protected $publicPath;

    /**
     * @var string
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
     * @param string|null $basePath
     */
    public function __construct($basePath = null)
    {
        if ($basePath !== null) {
            $this->setBasePath($basePath);
        }

        $this->registerPhandaAttachments();
        $this->registerPhandaServiceProviders();
        $this->registerPhandaAliases();
    }

    /**
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Registers the core Phanda attachments
     */
    protected function registerPhandaAttachments()
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(Container::class, $this);
    }

    /**
     * Registers the core Phanda Service Providers
     */
    protected function registerPhandaServiceProviders()
    {
        $this->register(new EventServiceProvider($this));
    }

    /**
     * Loads all providers in the phanda configuration file.
     * Defers the providers if needed.
     */
    public function registerProvidersInConfiguration()
    {
        /** @var array $providers */
        $providers = config('phanda.providers');
        $providerRepository = new ServiceProviderRepository($this);

        $providerRepository->loadProviders($providers);
    }

    /**
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        return $this->hasBootstrapped;
    }

    /**
     * @param Bootstrap[] $bootstrappers
     */
    public function bootstrapWith($bootstrappers)
    {
        $this->hasBootstrapped = true;

        foreach ($bootstrappers as $bootstrap) {
            $this->create($bootstrap)->bootstrap();
        }
    }

    /**
     * Binds the variables to their respective paths
     */
    protected function bindPathsInContainer()
    {
        $this->instance('path', $this->path());
        $this->instance('path.app', $this->appPath());
        $this->instance('path.assets', $this->assetsPath());
        $this->instance('path.bootstrap', $this->bootstrapPath());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.storage', $this->storagePath());
    }

    /**
     * Alias for appPath()
     *
     * @param string $path
     * @return string
     */
    public function path($path = '')
    {
        return $this->appPath($path);
    }

    /**
     * @param string $path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * @param string $path
     * @return string
     */
    public function appPath($path = '')
    {
        return $this->appPath . ($path ? DIRECTORY_SEPARATOR . $path : $path) ?:
            $this->basePath . DIRECTORY_SEPARATOR . 'app' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * @param string $path
     * @return string
     */
    public function assetsPath($path = '')
    {
        return $this->assetsPath . ($path ? DIRECTORY_SEPARATOR . $path : $path) ?:
            $this->basePath . DIRECTORY_SEPARATOR . 'assets' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * @param string $path
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return $this->bootstrapPath . ($path ? DIRECTORY_SEPARATOR . $path : $path) ?:
            $this->basePath . DIRECTORY_SEPARATOR . 'bootstrap' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * @param string $path
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->configPath . ($path ? DIRECTORY_SEPARATOR . $path : $path) ?:
            $this->basePath . DIRECTORY_SEPARATOR . 'config' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * @param string $path
     * @return string
     */
    public function publicPath($path = '')
    {
        return $this->publicPath . ($path ? DIRECTORY_SEPARATOR . $path : $path) ?:
            $this->basePath . DIRECTORY_SEPARATOR . 'public' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * @param string $path
     * @return string
     */
    public function storagePath($path = '')
    {
        return $this->storagePath . ($path ? DIRECTORY_SEPARATOR . $path : $path) ?:
            $this->basePath . DIRECTORY_SEPARATOR . 'storage' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setAppPath($path)
    {
        $this->appPath = $path;
        $this->instance('path.app', $path);
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setAssetsPath($path)
    {
        $this->assetsPath = $path;
        $this->instance('path.assets', $path);
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setBootstrapPath($path)
    {
        $this->bootstrapPath = $path;
        $this->instance('path.bootstrap', $path);
        return $this;
    }

    /**
     * @param string $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
        $this->bindPathsInContainer();
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setConfigPath($path)
    {
        $this->configPath = $path;
        $this->instance('path.config', $path);
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPublicPath($path)
    {
        $this->publicPath = $path;
        $this->instance('path.public', $path);
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setStoragePath($path)
    {
        $this->publicPath = $path;
        $this->instance('path.storage', $path);
        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironmentFile()
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * @return string
     */
    public function getPathToEnvironmentFile()
    {
        return $this->basePath();
    }

    /**
     * @return string
     */
    public function getEnvironmentFileFullPath()
    {
        return $this->getPathToEnvironmentFile() . DIRECTORY_SEPARATOR . $this->getEnvironmentFile();
    }

    /**
     * @param string $file
     * @return $this
     */
    public function setEnvironmentFile($file)
    {
        $this->environmentFile = $file;
        return $this;
    }

    /**
     * @return string
     */
    public function environment()
    {
        return $this['environment'];
    }

    /**
     * @param $environment
     * @return bool
     */
    public function checkEnvironment($environment)
    {
        return $this['environment'] === $environment;
    }

    /**
     * @param Closure $callback
     * @return string
     */
    public function discoverEnvironment(Closure $callback)
    {
        $args = $_SERVER['argv'] ?? null;

        return $this['environment'] = (new DiscoverEnvironment())->discover($callback, $args);
    }

    /**
     * @return bool
     */
    public function inConsole()
    {
        if (isset($_ENV['APP_IN_CONSOLE'])) {
            return $_ENV['APP_IN_CONSOLE'] === true;
        }

        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return file_exists($this->storagePath() . '/framework/down');
    }

    /**
     * @return void
     */
    public function start()
    {
        if ($this->started) {
            return;
        }

        $this->triggerAppCallbacks($this->startingCallbacks);

        array_walk($this->serviceProviders, function ($provider) {
            $this->startProvider($provider);
        });

        $this->started = true;

        $this->triggerAppCallbacks($this->startedCallbacks);
    }

    /**
     * @param mixed $callback
     * @return void
     */
    public function starting($callback)
    {
        $this->startingCallbacks[] = $callback;
    }

    /**
     * @param mixed $callback
     * @return void
     */
    public function started($callback)
    {
        $this->startedCallbacks = $callback;
    }

    public function hasStarted()
    {
        return $this->started;
    }

    /**
     * @param SymfonyRequest $request
     * @param int $type
     * @param bool $catch
     * @return Response
     */
    public function handle(SymfonyRequest $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        /** @var HttpKernelContract $kernel */
        $kernel = $this[HttpKernelContract::class];
        return $kernel->handle(Request::createFromSymfonyRequest($request));
    }

    /**
     * @param string|AbstractServiceProvider $provider
     * @param bool $force
     * @return AbstractServiceProvider
     */
    public function register($provider, $force = false)
    {
        if (($registered = $this->getProvider($provider)) && !$force) {
            return $registered;
        }

        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        $this->markAsRegistered($provider);

        if ($this->started) {
            $this->startProvider($provider);
        }

        return $provider;
    }

    /**
     * @param string|AbstractServiceProvider $provider
     * @return AbstractServiceProvider|null
     */
    public function getProvider($provider)
    {
        return array_values($this->getProviders($provider))[0] ?? null;
    }

    /**
     * @param string|AbstractServiceProvider $provider
     * @return array
     */
    public function getProviders($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);
        return PhandArr::filter($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        });
    }

    /**
     * @param string $provider
     * @return AbstractServiceProvider
     */
    public function resolveProvider($provider)
    {
        return new $provider($this);
    }

    /**
     * @param AbstractServiceProvider $provider
     */
    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[] = $provider;
        $this->loadedProviders[get_class($provider)] = true;
    }

    /**
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public function create($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        // Extra logic for handling the create if needed.

        return parent::create($abstract, $parameters);
    }

    /**
     * @param AbstractServiceProvider $provider
     * @return mixed
     */
    protected function startProvider(AbstractServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }

        return null;
    }

    /**
     * @param array $callbacks
     */
    protected function triggerAppCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callback($this);
        }
    }

    /**
     * Terminates a request, and throws an exception with the given code.
     *
     * @param $code
     * @param string $message
     * @param array $headers
     */
    public function halt($code, $message = '', array $headers = [])
    {
        if ($code == 404) {
            throw new NotFoundHttpException($message);
        }

        throw new HttpException($code, $message, null, $headers);
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function stopping(callable $callback)
    {
        $this->stoppingCallbacks[] = $callback;
        return $this;
    }

    /**
     * Stops the application
     */
    public function stop()
    {
        foreach ($this->stoppingCallbacks as $stopping) {
            $this->call($stopping);
        }
    }

    /**
     * @return array
     */
    public function getLoadedProviders()
    {
        return $this->loadedProviders;
    }

    /**
     * Registers core phanda aliases
     */
    public function registerPhandaAliases()
    {
        foreach ([
                     'app' => [Application::class, Container::class, ApplicationContract::class, ContainerInterface::class],
                     'config' => [ConfigurationRepository::class],
                     'events' => [Dispatcher::class, \Phanda\Contracts\Events\Dispatcher::class],
                     'request' => [Request::class, SymfonyRequest::class]
                 ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

    /**
     * Resets the application, and internal variables
     */
    public function reset()
    {
        parent::reset();

        $this->buildQueue = [];
        $this->loadedProviders = [];
        $this->startingCallbacks = [];
        $this->startedCallbacks = [];
        $this->stoppingCallbacks = [];
        $this->serviceProviders = [];
    }

    /**
     * Gets the application namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        if (!is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        foreach ((array)data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array)$path as $pathChoice) {
                if (realpath(app_path()) == realpath(base_path() . '/' . $pathChoice)) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }
}