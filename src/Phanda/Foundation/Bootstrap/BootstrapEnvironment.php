<?php

namespace Phanda\Foundation\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidPathException;
use Phanda\Foundation\Application;
use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use SplFileInfo;
use Symfony\Component\Console\Input\ArgvInput;
use Phanda\Environment\Repository as EnvironmentRepository;
use Symfony\Component\Finder\Finder;

class BootstrapEnvironment implements Bootstrap
{
    /**
     * @param Application $phanda
     * @return void
     *
     * @throws \Exception
     */
    public function bootstrap(Application $phanda)
    {
        $environment = new EnvironmentRepository();
        $phanda->instance('environment', $environment);
        $this->loadEnvironment($phanda, $environment);
    }

    /**
     * @param Application $phanda
     * @param EnvironmentRepository $repository
     *
     * @throws \Exception
     */
    protected function loadEnvironment(Application $phanda, EnvironmentRepository $repository)
    {
        $files = $this->getEnvironmentFiles($phanda);

        if(!isset($files[$phanda->getAppEnvironmentFile()])) {
            throw new \Exception('Could not find application environment file.');
        }

        foreach($files as $key => $path) {
            $dotEnv = (new Dotenv(dirname($path), basename($path)));
            $repository->set($key, $dotEnv->load());
        }
    }

    /**
     * @param Application $phanda
     * @return array
     */
    protected function getEnvironmentFiles(Application $phanda)
    {
        $files = [];
        $environmentPath = $phanda->environmentPath();

        foreach(Finder::create()->files()->name('*.env')->in($environmentPath) as $file)
        {
            /** @var SplFileInfo $file */
            $directory = $this->getNestedDirectory($file, $environmentPath);
            $files[$directory . basename($file->getRealPath(), '.env')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);
        return $files;
    }

    /**
     * @param SplFileInfo $file
     * @param string $environmentPath
     * @return string
     */
    protected function getNestedDirectory(SplFileInfo $file, $environmentPath)
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($environmentPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }

        return $nested;
    }
}