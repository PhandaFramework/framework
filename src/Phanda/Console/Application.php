<?php

namespace Phanda\Console;

use Closure;
use Phanda\Console\Events\CommandFinishedEvent;
use Phanda\Console\Events\CommandStartingEvent;
use Phanda\Console\Events\KungfuStartingEvent;
use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Events\Dispatcher;
use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use Phanda\Foundation\Bootstrap\BootstrapConfig;
use Phanda\Foundation\Bootstrap\BootstrapEnvironment;
use Phanda\Foundation\Bootstrap\BootstrapPhanda;
use Phanda\Foundation\Bootstrap\BootstrapProviders;
use Phanda\Support\ProcessUtils;
use Symfony\Component\Console\Application as SymfonyApplication;
use Phanda\Contracts\Console\Application as ConsoleApplicationContract;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class Application extends SymfonyApplication implements ConsoleApplicationContract
{
    /**
     * @var Container
     */
    protected $phanda;

    /**
     * @var BufferedOutput
     */
    protected $lastOutput;

    /**
     * @var Bootstrap[]
     */
    protected $bootstrapers = [];

    /**
     * @var array
     */
    protected static $bootstrapCallbacks = [];

    /**
     * @var Dispatcher
     */
    protected $eventDispatcher;

    /**
     * Application constructor.
     * @param Container $phanda
     * @param Dispatcher $eventDispatcher
     * @param $version
     */
    public function __construct(Container $phanda, Dispatcher $eventDispatcher, $version)
    {
        parent::__construct('Phanda Framework', $version);

        $this->phanda = $phanda;
        $this->eventDispatcher = $eventDispatcher;
        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        $this->eventDispatcher->dispatch('kungfuStarting', new KungfuStartingEvent($this));

        $this->bootstrap();
    }

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     *
     * @throws \Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $commandName = $this->getCommandName(
            $input = $input ?: new ArgvInput
        );

        $this->eventDispatcher->dispatch('commandStarting', new CommandStartingEvent(
            $commandName,
            $input,
            $output = $output ?: new ConsoleOutput
        ));

        $exitCode = parent::run($input, $output);

        $this->eventDispatcher->dispatch('commandFinished', new CommandFinishedEvent(
            $commandName,
            $input,
            $output,
            $exitCode
        ));

        return $exitCode;
    }

    /**
     * @return string
     */
    public static function phpBinary()
    {
        return ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false));
    }

    /**
     * @return string
     */
    public static function artisanBinary()
    {
        return defined('KUNGFU_BINARY') ? ProcessUtils::escapeArgument(KUNGFU_BINARY) : 'kungfu';
    }

    /**
     * @param $string
     * @return string
     */
    public static function formatCommandString($string)
    {
        return sprintf('%s %s %s', static::phpBinary(), static::artisanBinary(), $string);
    }

    /**
     * @param Closure $callback
     */
    public static function starting(Closure $callback)
    {
        static::$bootstrapCallbacks[] = $callback;
    }

    /**
     * Bootstraps the console application
     */
    protected function bootstrap()
    {
        foreach (static::$bootstrapCallbacks as $callback) {
            $callback($this);
        }
    }

    public static function clearBootstrapCallbacks()
    {
        static::$bootstrapCallbacks = [];
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
        if (is_subclass_of($command, SymfonyCommand::class)) {
            $command = $this->phanda->create($command)->getName();
        }

        if (!$this->has($command)) {
            throw new CommandNotFoundException(sprintf('The command "%s" does not exist.', $command));
        }

        array_unshift($parameters, $command);
        $this->lastOutput = $outputBuffer ?: new BufferedOutput;
        $this->setCatchExceptions(false);
        $result = $this->run(new ArrayInput($parameters), $this->lastOutput);
        $this->setCatchExceptions(true);

        return $result;
    }

    /**
     * Get the output from the last command.
     *
     * @return string
     */
    public function output()
    {
        return $this->lastOutput && method_exists($this->lastOutput, 'fetch')
            ? $this->lastOutput->fetch()
            : '';
    }

    /**
     * @param SymfonyCommand $command
     * @return null|SymfonyCommand
     */
    public function add(SymfonyCommand $command)
    {
        if ($command instanceof ConsoleCommand) {
            $command->setPhanda($this->phanda);
        }

        return $this->addToParent($command);
    }

    /**
     * @param SymfonyCommand $command
     * @return null|SymfonyCommand
     */
    protected function addToParent(SymfonyCommand $command)
    {
        return parent::add($command);
    }

    /**
     * @param $command
     * @return null|SymfonyCommand
     */
    public function resolve($command)
    {
        return $this->add($this->phanda->create($command));
    }

    /**
     * @param $commands
     * @return $this
     */
    public function resolveCommands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();
        foreach ($commands as $command) {
            $this->resolve($command);
        }
        return $this;
    }

    /**
     * Get the default input definition for the application.
     *
     * This is used to add the --env option to every available command.
     *
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        return modify(parent::getDefaultInputDefinition(), function ($definition) {
            /** @var InputDefinition $definition */
            $definition->addOption($this->getEnvironmentOption());
        });
    }

    /**
     * Get the global environment option for the definition.
     *
     * @return \Symfony\Component\Console\Input\InputOption
     */
    protected function getEnvironmentOption()
    {
        $message = 'The environment the command should run under';
        return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message);
    }

    /**
     * @return Container
     */
    public function getPhanda()
    {
        return $this->phanda;
    }
}