<?php

namespace Phanda\Console;

use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Support\PhandaStr;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ConsoleCommand extends SymfonyCommand
{

    /**
     * @var \Phanda\Contracts\Foundation\Application
     */
    protected $phanda;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var PhandaOutputStyle
     */
    protected $output;

    /**
     * @var string
     */
    protected $signature;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * The default verbosity of output commands.
     *
     * @var int
     */
    protected $verbosity = OutputInterface::VERBOSITY_NORMAL;

    /**
     * The mapping between human readable verbosity levels and Symfony's OutputInterface.
     *
     * @var array
     */
    protected $verbosityMap = [
        'v' => OutputInterface::VERBOSITY_VERBOSE,
        'vv' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv' => OutputInterface::VERBOSITY_DEBUG,
        'quiet' => OutputInterface::VERBOSITY_QUIET,
        'normal' => OutputInterface::VERBOSITY_NORMAL,
    ];

    /**
     * ConsoleCommand constructor.
     */
    public function __construct()
    {
        if (isset($this->signature)) {
            $this->configurePhandaCommand();
        } else {
            parent::__construct($this->name);
        }

        $this->setDescription($this->description);
        $this->setHidden($this->isHidden());

        if (!isset($this->signature)) {
            $this->specifyParameters();
        }
    }

    /**
     * Configures the phanda command using the command parser
     */
    protected function configurePhandaCommand()
    {
        [$name, $arguments, $options] = CommandParser::parse($this->signature);

        parent::__construct($this->name = $name);
        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }

    /**
     * Specifies args and options for a command
     */
    protected function specifyParameters()
    {
        foreach ($this->getArguments() as $arguments) {
            call_user_func_array([$this, 'addArgument'], $arguments);
        }

        foreach ($this->getOptions() as $options) {
            call_user_func_array([$this, 'addOption'], $options);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     *
     * @throws \Exception
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->output = $this->phanda->create(
            PhandaOutputStyle::class, ['input' => $input, 'output' => $output]
        );

        return parent::run(
            $this->input = $input, $this->output
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->phanda->call([$this, 'handle']);
    }

    /**
     * @param $command
     * @param array $arguments
     * @return int
     *
     * @throws \Exception
     */
    public function call($command, array $arguments = [])
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run(
            $this->createInputFromArguments($arguments), $this->output
        );
    }

    /**
     * @param $command
     * @param array $arguments
     * @return int
     *
     * @throws \Exception
     */
    public function callSilent($command, array $arguments = [])
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run(
            $this->createInputFromArguments($arguments), new NullOutput
        );
    }

    /**
     * @param array $arguments
     * @return ArrayInput
     */
    protected function createInputFromArguments(array $arguments)
    {
        return modify(new ArrayInput($arguments), function ($input) {
            /** @var ArrayInput $input */
            if ($input->hasParameterOption(['--no-interaction'], true)) {
                $input->setInteractive(false);
            }
        });
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }

    /**
     * @param string|null $key
     * @return array|null|string
     */
    public function getInputArgument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * @return array
     */
    public function getInputArguments()
    {
        return $this->getInputArgument();
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return [];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * @param string|null $key
     * @return array|null|string
     */
    public function getOption($key = null)
    {
        if(is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * @return array
     */
    public function options()
    {
        return $this->getOption();
    }

    public function getOptions()
    {
        return [];
    }

    /**
     * @param string $question
     * @param bool $default
     * @return string|null
     */
    public function confirm($question, $default = null)
    {
        return $this->output->confirm($question, $default ?? false);
    }

    /**
     * @param $question
     * @param bool $default
     * @return string|null
     */
    public function ask($question, $default = null)
    {
        return $this->output->ask($question, $default);
    }

    /**
     * @param $question
     * @param array $choices
     * @param string|null $default
     * @return mixed
     */
    public function askWithCompletion($question, array $choices, $default = null)
    {
        $question = new Question($question, $default);
        $question->setAutocompleterValues($choices);
        return $this->output->askQuestion($question);
    }

    /**
     * @param $question
     * @param bool $fallback
     * @return mixed
     */
    public function askSecret($question, $fallback = true)
    {
        $question = new Question($question);
        $question->setHidden(true)->setHiddenFallback($fallback);
        return $this->output->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param  string  $question
     * @param  array   $choices
     * @param  string|null  $default
     * @param  mixed|null   $attempts
     * @param  bool|null    $multiple
     * @return string
     */
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = null)
    {
        $question = new ChoiceQuestion($question, $choices, $default);
        $question->setMaxAttempts($attempts)->setMultiselect($multiple);
        return $this->output->askQuestion($question);
    }

    /**
     * Format input to textual table.
     *
     * @param  array   $headers
     * @param  Arrayable|array  $rows
     * @param  string  $tableStyle
     * @param  array   $columnStyles
     * @return void
     */
    public function table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders((array) $headers)->setRows($rows)->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }

    /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as standard output.
     *
     * @param  string  $string
     * @param  string  $style
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function line($string, $style = null, $verbosity = null)
    {
        $styled = $style ? "<$style>$string</$style>" : $string;

        $this->output->writeln($styled, $this->parseVerbosity($verbosity));
    }

    /**
     * Write a string as comment output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Write a string as question output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function question($string, $verbosity = null)
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function error($string, $verbosity = null)
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * Write a string as warning output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function warning($string, $verbosity = null)
    {
        if (! $this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosity);
    }

    /**
     * Write a string in an alert box.
     *
     * @param  string  $string
     * @return void
     */
    public function alert($string)
    {
        $length = PhandaStr::length(strip_tags($string)) + 12;

        $this->comment(str_repeat('*', $length));
        $this->comment('*     '.$string.'     *');
        $this->comment(str_repeat('*', $length));

        $this->output->newLine();
    }

    /**
     * Set the verbosity level.
     *
     * @param  string|int  $level
     * @return void
     */
    protected function setVerbosity($level)
    {
        $this->verbosity = $this->parseVerbosity($level);
    }

    /**
     * Get the verbosity level in terms of Symfony's OutputInterface level.
     *
     * @param  string|int|null  $level
     * @return int
     */
    protected function parseVerbosity($level = null)
    {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (! is_int($level)) {
            $level = $this->verbosity;
        }

        return $level;
    }

    /**
     * @return PhandaOutputStyle
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return \Phanda\Contracts\Foundation\Application
     */
    public function getPhanda()
    {
        return $this->phanda;
    }

    /**
     * @param Container $phanda
     */
    public function setPhanda($phanda)
    {
        $this->phanda = $phanda;
    }

}