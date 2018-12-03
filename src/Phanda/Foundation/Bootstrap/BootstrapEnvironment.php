<?php

namespace Phanda\Foundation\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidPathException;
use Phanda\Foundation\Application;
use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use Symfony\Component\Console\Input\ArgvInput;

class BootstrapEnvironment implements Bootstrap
{
    /**
     * @param Application $phanda
     * @return void
     */
    public function bootstrap(Application $phanda)
    {
        $this->checkEnvironmentFile($phanda);

        try {
            (new Dotenv($phanda->getPathToEnvironmentFile(), $phanda->getEnvironmentFile()))->load();
        } catch (InvalidPathException $e) {
            echo 'The path to the environment file is invalid: ' . $e->getMessage();
            die(1);
        } catch (InvalidFileException $e) {
            echo 'The environment file is invalid: ' . $e->getMessage();
            die(1);
        }
    }

    /**
     * @param Application $phanda
     */
    protected function checkEnvironmentFile(Application $phanda)
    {
        if ($phanda->inConsole() && ($input = new ArgvInput)->hasParameterOption('--env')) {
            if ($this->setApplicationEnvironmentFile($phanda, $phanda->getEnvironmentFile() . '.' . $input->getParameterOption('--env'))) {
                return;
            }
        }

        if (!environment('APPLICATION_ENVIRONMENT')) {
            return;
        }

        $this->setApplicationEnvironmentFile($phanda, $phanda->getEnvironmentFile() . '.' . environment('APP_ENV'));
    }

    /**
     * @param Application $phanda
     * @param $file
     * @return bool
     */
    protected function setApplicationEnvironmentFile(Application $phanda, $file)
    {
        if (file_exists($phanda->getPathToEnvironmentFile() . '/' . $file)) {
            $phanda->setEnvironmentFile($file);

            return true;
        }

        return false;
    }
}