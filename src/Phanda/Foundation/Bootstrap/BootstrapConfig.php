<?php

namespace Phanda\Foundation\Bootstrap;

use Exception;
use Phanda\Configuration\Repository as ConfigurationRepository;
use Phanda\Contracts\Foundation\Bootstrap\Bootstrap;
use Phanda\Foundation\Application;
use \SplFileInfo;
use Symfony\Component\Finder\Finder;

class BootstrapConfig implements Bootstrap
{
    /**
     * @param Application $phanda
     * @return void
     * @throws Exception
     */
    public function bootstrap(Application $phanda)
    {
        $config = new ConfigurationRepository();
        $phanda->instance('config', $config);
        $this->loadConfiguration($phanda, $config);
        $phanda->discoverEnvironment(function() use ($config) {
           return $config->get('phanda.environment', 'production');
        });

        date_default_timezone_set($config->get('phanda.timezone', 'UTC'));
        mb_internal_encoding('UTF-8');
    }

    /**
     * @param Application $phanda
     * @param ConfigurationRepository $repository
     * @throws Exception
     */
    protected function loadConfiguration(Application $phanda, ConfigurationRepository $repository)
    {
        $files = $this->getConfigurationFiles($phanda);

        if (! isset($files['phanda'])) {
            throw new Exception('Unable to load the core "phanda" configuration file.');
        }

        foreach ($files as $key => $path) {
            $repository->set($key, require $path);
        }
    }

    /**
     * @param Application $phanda
     * @return array
     */
    protected function getConfigurationFiles(Application $phanda)
    {
        $files = [];
        $configurationPath = $phanda->configPath();

        foreach(Finder::create()->files()->name('*.php')->in($configurationPath) as $file)
        {
            /** @var SplFileInfo $file */
            $directory = $this->getNestedDirectory($file, $configurationPath);
            $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);
        return $files;
    }

    /**
     * @param SplFileInfo $file
     * @param string $configPath
     * @return string
     */
    protected function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }

        return $nested;
    }
}