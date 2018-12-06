<?php

namespace Phanda\Support\Facades;

use Phanda\Contracts\Foundation\Application;

abstract class Facade
{
    /**
     * @var Application
     */
    protected static $phanda;

    /**
     * @var array
     */
    protected static $implementations;

    /**
     * @var array
     */
    protected static $resolvedImplementations;

    /**
     * @var array
     */
    protected static $resolved;


    /**
     * @param string $name
     * @param mixed $implementation
     */
    public static function addImplementation($name, $implementation)
    {
        static::$implementations[static::getFacadeName()][$name] = $implementation;

        if (isset(static::$phanda)) {
            static::$phanda->instance($name, $implementation);
        }
    }

    /**
     * @param string $name
     */
    public static function removeImplementation($name)
    {
        if (isset(static::$implementations[static::getFacadeName()][$name])) {
            unset(static::$implementations[static::getFacadeName()][$name]);
        }

        if (isset(static::$resolvedImplementations[static::getFacadeName()][$name])) {
            unset(static::$resolvedImplementations[static::getFacadeName()][$name]);
        }

        if (isset(static::$phanda) && static::$phanda->isAttached($name)) {
            static::$phanda->clearInstance($name);
        }
    }

    /**
     * Removes all implementations on a given Facade
     */
    public static function removeAllImplementations()
    {
        if (isset(static::$phanda)) {
            foreach (static::$resolvedImplementations[static::getFacadeName()] as $name => $implementation) {
                static::$phanda->clearInstance($name);
            }
        }

        static::$implementations[static::getFacadeName()] = [];
        static::$resolvedImplementations[static::getFacadeName()] = [];
    }

    /**
     * Set up the name of the Facade instance being resolved. Used internally for checking if the Facade has been
     * resolved or not.
     *
     * @return string
     */
    protected abstract static function getFacadeName(): string;

    /**
     * Sets up the facade implementations by calling static::addImplementation($name, $implementation) for each of the
     * implementations this Facade has.
     *
     * @return void
     */
    protected abstract static function setupFacadeImplementations();

    /**
     * @return array
     */
    public static function getFacadeImplementations()
    {
        if (!static::$resolved[static::getFacadeName()]) {
            static::setupFacadeImplementations();
            return static::resolveImplementations();
        }

        return static::$resolvedImplementations[static::getFacadeName()];
    }

    /**
     * @return array
     */
    protected static function resolveImplementations()
    {
        foreach (static::$implementations[static::getFacadeName()] as $name => $implementation) {
            if (isset(static::$resolvedImplementations[static::getFacadeName()][$name])) {
                continue;
            }

            if (is_object($implementation)) {
                static::$resolvedImplementations[static::getFacadeName()][$name] = $implementation;
            } else {
                static::$resolvedImplementations[static::getFacadeName()][$name] = static::$phanda->create($implementation);
            }
        }

        static::$resolved[static::getFacadeName()] = true;
        return static::$resolvedImplementations[static::getFacadeName()];
    }

    /**
     * @return Application
     */
    public static function getFacadePhandaInstance()
    {
        return static::$phanda;
    }

    /**
     * @param Application $phanda
     */
    public static function setFacadePhandaInstance(Application $phanda)
    {
        static::$phanda = $phanda;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($name, $arguments)
    {
        $implementations = static::getFacadeImplementations();

        if(!$implementations) {
            throw new \RuntimeException("Facade has not been setup correctly, or has defined no implementations.");
        }

        foreach($implementations as $implementation)
        {
            if(method_exists($implementation, $name)) {
                return $implementation->$name(...$arguments);
            }
        }

        throw new \RuntimeException("Method {$name} not found on Facade " . static::getFacadeName());
    }
}