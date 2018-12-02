<?php


namespace Phanda\Foundation\Console;

use Closure;
use Phanda\Contracts\Console\Kernel as ConsoleKernel;
use Phanda\Contracts\Events\Dispatcher;
use Phanda\Contracts\Foundation\Application;

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
     * @var bool
     */
    protected $commandsLoaded = false;

    public function __construct(Application $phanda, Dispatcher $eventDispatcher)
    {
        if (! defined('KUNGFU_BINARY')) {
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
            $this->reportException($e);
            $this->outputException($e);
            return 1;
        } catch (\Throwable $e) {
            $this->reportException($e);
            $this->outputException($e);
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

    }

    protected function loadCommandsInDir($path)
    {

    }

    /**
     * Run an Artisan console command by name.
     *
     * @param  string $command
     * @param  array $parameters
     * @param  \Symfony\Component\Console\Output\OutputInterface|null $outputBuffer
     * @return int
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        // TODO: Implement call() method.
    }

    /**
     * Get all of the commands registered with the console.
     *
     * @return array
     */
    public function all()
    {
        // TODO: Implement all() method.
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        // TODO: Implement output() method.
    }

    /**
     * @param $input
     * @param $status
     * @return mixed
     */
    public function stop($input, $status)
    {
        $this->phanda->stop();
    }
}