<?php


namespace Phanda\Foundation\Console;

use Closure;
use Phanda\Console\Application as Kungfu;
use Phanda\Console\ClosureCommand;
use Phanda\Console\ConsoleCommand;
use Phanda\Contracts\Console\Kernel as ConsoleKernel;
use Phanda\Contracts\Events\Dispatcher;
use Phanda\Contracts\Foundation\Application;
use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use Phanda\Exceptions\ExceptionHandler;
use Phanda\Foundation\Bootstrap\BootstrapConfig;
use Phanda\Foundation\Bootstrap\BootstrapEnvironment;
use Phanda\Foundation\Bootstrap\BootstrapExceptionHandler;
use Phanda\Foundation\Bootstrap\BootstrapFacades;
use Phanda\Foundation\Bootstrap\BootstrapPhanda;
use Phanda\Foundation\Bootstrap\BootstrapProviders;
use Phanda\Support\PhandArr;
use Phanda\Support\PhandaStr;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Kernel implements ConsoleKernel
{
    /**
     * @var Application
     */
    protected $phanda;

    /**
     * @var Dispatcher
     */
    protected $eventDispatcher;

    /**
     * @var \Phanda\Console\Application
     */
    protected $kungfu;

    /**
     * @var array
     */
    protected $commands = [];

    /**
     * @var array
     */
    protected $consoleBootstrappers = [
        BootstrapEnvironment::class,
        BootstrapConfig::class,
        BootstrapExceptionHandler::class,
        BootstrapProviders::class,
        BootstrapFacades::class,
        BootstrapPhanda::class
    ];

    /**
     * @var bool
     */
    protected $commandsLoaded = false;

    public function __construct(Application $phanda, Dispatcher $eventDispatcher)
    {
        if (!defined('KUNGFU_BINARY')) {
            define('KUNGFU_BINARY', 'kungfu');
        }

        $this->phanda = $phanda;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Handle an incoming console command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface $input
     * @param  \Symfony\Component\Console\Output\OutputInterface|null $output
     * @return int
     */
    public function handle($input, $output = null)
    {
        try {
            $this->bootstrap();
            return $this->getKungfu()->run($input, $output);
        } catch (\Exception $e) {
            $this->saveException($e);
            $this->outputException($output, $e);
            return 1;
        } catch (\Throwable $e) {
            $this->saveException($e);
            $this->outputException($output, $e);
            return 1;
        }
    }

    /**
     * Registers commands and their callbacks.
     */
    protected function commands()
    {
        //
    }

    public function command($signature, Closure $callback)
    {
        $command = new ClosureCommand($signature, $callback);

        Kungfu::starting(function ($kungfu) use ($command) {
            /** @var Kungfu $kungfu */
            $kungfu->add($command);
        });

        return $command;
    }

    /**
     * @param array|string $paths
     *
     * @throws \ReflectionException
     */
    protected function loadCommandsInDir($paths)
    {
    	$this->kungfu->loadCommandsInDir($paths);
    }

    /**
     * @param Command $command
     */
    public function registerCommand($command)
    {
        $this->getKungfu()->add($command);
    }

    /**
     * Run an Kungfu console command by name.
     *
     * @param  string $command
     * @param  array $parameters
     * @param  \Symfony\Component\Console\Output\OutputInterface|null $outputBuffer
     * @return int
     *
     * @throws \Exception
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        $this->bootstrap();
        return $this->getKungfu()->call($command, $parameters, $outputBuffer);
    }

    /**
     * Get all of the commands registered with the console.
     *
     * @return array
     */
    public function all()
    {
        $this->bootstrap();
        return $this->getKungfu()->all();
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        $this->bootstrap();
        return $this->getKungfu()->output();
    }

    /**
     * @param $input
     * @param $status
     */
    public function stop($input, $status)
    {
        $this->phanda->stop();
    }

    /**
     * @return Kungfu
     */
    protected function getKungFu()
    {
        if (is_null($this->kungfu)) {
            return $this->kungfu = (new Kungfu($this->phanda, $this->eventDispatcher, $this->phanda->version()))
                ->resolveCommands($this->commands);
        }

        return $this->kungfu;
    }

    /**
     * @param Kungfu $kungfu
     */
    public function setKungfu($kungfu)
    {
        $this->kungfu = $kungfu;
    }

    /**
     * Bootstraps the commands
     */
    protected function bootstrap()
    {
        if(!$this->phanda->hasBeenBootstrapped()) {
            $this->phanda->bootstrapWith($this->getBootstrappers());
        }

        if (!$this->commandsLoaded) {
            $this->commands();
            $this->commandsLoaded = true;
        }
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    public function getBootstrappers()
    {
        return $this->consoleBootstrappers;
    }

    /**
     * Adds a bootstrapper to the console
     *
     * @param string $bootstrapper
     * @return $this
     */
    public function addBootstrapper($bootstrapper) {
        $this->consoleBootstrappers[] = $bootstrapper;
        return $this;
    }

    /**
     * Advanced use only: Sets the console bootstrappers
     *
     * @param array $bootstrappers
     * @return $this
     */
    public function setBootstrappers(array $bootstrappers) {
        $this->consoleBootstrappers = $bootstrappers;
        return $this;
    }

    /**
     * Advanced use only: Clears the console bootstrappers
     *
     * @return $this
     */
    public function clearBootstrappers()
    {
        $this->consoleBootstrappers = [];
        return $this;
    }

    /**
     * @param \Exception $e
     */
    protected function saveException($e)
    {
        /** @var ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->phanda->create(ExceptionHandler::class);
        $exceptionHandler->save($e);
    }

    /**
     * @param OutputInterface $output
     * @param \Exception $e
     */
    protected function outputException(OutputInterface $output, \Exception $e)
    {
        /** @var ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->phanda[ExceptionHandler::class];
        $exceptionHandler->outputToKungfu($output, $e);
    }
}