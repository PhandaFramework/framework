<?php

namespace Phanda\Console;

use Closure;
use ReflectionFunction;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClosureCommand extends ConsoleCommand
{
    /**
     * @var Closure
     */
    protected $callback;

    public function __construct($signature, Closure $callback)
    {
        $this->signature = $signature;
        $this->callback = $callback;
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     *
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputs = array_merge($input->getArguments(), $input->getOptions());

        $parameters = [];

        foreach ((new ReflectionFunction($this->callback))->getParameters() as $parameter) {
            if (isset($inputs[$parameter->name])) {
                $parameters[$parameter->name] = $inputs[$parameter->name];
            }
        }

        return $this->phanda->call(
            $this->callback->bindTo($this, $this), $parameters
        );
    }

    /**
     * @param string $description
     * @return $this
     */
    public function describeCommand($description)
    {
        $this->setDescription($description);
        return $this;
    }
}