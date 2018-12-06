<?php

namespace Phanda\Container;

class AliasLoader
{

    /**
     * @var array
     */
    protected $aliases;

    /**
     * @var bool
     */
    protected $hasRegistered = false;

    /**
     * @var AliasLoader
     */
    protected static $instance;

    /**
     * AliasLoader constructor.
     *
     * @param array $aliases
     */
    public function __construct(array $aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * @param array $aliases
     * @return AliasLoader
     */
    public static function getInstance(array $aliases = [])
    {
        if (!isset(static::$instance)) {
            return static::$instance = new static($aliases);
        }

        $aliases = array_merge(static::$instance->getAliases(), $aliases);
        static::$instance->setAliases($aliases);
        return static::$instance;
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @param array $aliases
     * @return $this
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRegistered()
    {
        return $this->hasRegistered;
    }

    /**
     * Registers the aliases
     */
    public function register()
    {
        if (!$this->hasRegistered) {
            $this->setupSplAutoloader();
            $this->hasRegistered = true;
        }
    }

    /**
     * Initializes the SPL autoloader.
     */
    public function setupSplAutoloader()
    {
        spl_autoload_register([$this, 'load'], true, true);
    }

    public function load($alias)
    {
        require $this->loadFromCache($alias);
        return true;
    }



}