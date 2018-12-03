<?php

namespace Phanda\Foundation\Console\Commands;

use Phanda\Console\ConsoleCommand;
use Phanda\Support\ProcessUtils;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\PhpExecutableFinder;

class ServeCommand extends ConsoleCommand
{

    /**
     * @var string
     */
    protected $name = 'serve';

    /**
     * @var string
     */
    protected $description = 'Start a development PHP server, which serves your Phanda Application.';

    public function handle()
    {
        chdir(public_path());
        $this->line("<info>Phanda PHP Development Server. Running at:</info> <http://{$this->host()}:{$this->port()}>");
        passthru($this->serverCommand(), $status);

        return $status;
    }

    protected function serverCommand() {
        return sprintf('%s -S %s:%s %s',
            ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false)),
            $this->host(),
            $this->port(),
            ProcessUtils::escapeArgument(bootstrap_path('console/server.php'))
        );
    }

    /**
     * Get the host for the command.
     *
     * @return string
     */
    protected function host()
    {
        return $this->input->getOption('host');
    }

    /**
     * Get the port for the command.
     *
     * @return string
     */
    protected function port()
    {
        return $this->input->getOption('port');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on', '127.0.0.1'],

            ['port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on', 8000],
        ];
    }
}