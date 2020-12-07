<?php

namespace Vectorface\SnappyRouter\Config;

use ArrayAccess;
use Exception;

/**
 * A wrapper object to the SnappyRouter configuration.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class Config implements ArrayAccess, ConfigInterface
{
    /** the config key for the list of handlers */
    const KEY_HANDLERS = 'handlers';
    /** the config key for the DI provider */
    const KEY_DI = 'di';
    /** the config key for the list of handler options */
    const KEY_OPTIONS = 'options';
    /** the config key for a class */
    const KEY_CLASS = 'class';
    /** the config key for a file */
    const KEY_FILE = 'file';
    /** the config key for the list of services (deprecated) */
    const KEY_SERVICES = 'services';
    /** the config key for the list of controllers */
    const KEY_CONTROLLERS = 'services';
    /** the config key for the list of plugins */
    const KEY_PLUGINS = 'plugins';
    /** the config key for the list of controller namespaces */
    const KEY_NAMESPACES = 'namespaces';
    /** the config key for the list of controller folders */
    const KEY_FOLDERS = 'folders';
    /** the config key for the list of tasks */
    const KEY_TASKS = 'tasks';
    /** the config key for debug mode */
    const KEY_DEBUG = 'debug';

    // the internal config array
    private $config;

    /**
     * Constructor for the class.
     * @param mixed $config An array of config settings (or something that easily
     *        typecasts to an array like an stdClass).
     */
    public function __construct($config)
    {
        $this->config = (array)$config;
    }

    /**
     * Returns whether or not the given key exists in the config.
     * @param string $offset The key to be checked.
     * @return bool Returns true if the key exists and false otherwise.
     */
    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    /**
     * Returns the value associated with the key or null if no value exists.
     * @param string $offset The key to be fetched.
     * @return bool Returns the value associated with the key or null if no value exists.
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->config[$offset] : null;
    }

    /**
     * Sets the value associated with the given key.
     *
     * @param string $offset The key to be used.
     * @param mixed $value The value to be set.
     * @throws Exception
     */
    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            throw new Exception('Config values must contain a key.');
        }
        $this->config[$offset] = $value;
    }

    /**
     * Removes the value set to the given key.
     * @param string $offset The key to unset.
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }

    /**
     * Returns the value associated with the given key. An optional default value
     * can be provided and will be returned if no value is associated with the key.
     * @param string $key The key to be used.
     * @param mixed $defaultValue The default value to return if the key currently
     *        has no value associated with it.
     * @return mixed Returns the value associated with the key or the default value if
     *         no value is associated with the key.
     */
    public function get($key, $defaultValue = null)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : $defaultValue;
    }

    /**
     * Sets the current value associated with the given key.
     *
     * @param string $key The key to be set.
     * @param mixed $value The value to be set to the key.
     * @throws Exception
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Returns an array representation of the whole configuration.
     * @return array An array representation of the whole configuration.
     */
    public function toArray()
    {
        return $this->config;
    }

    /**
     * Returns whether or not we are in debug mode.
     * @return boolean Returns true if the router is in debug mode and false
     *         otherwise.
     */
    public function isDebug()
    {
        return (bool)$this->get(self::KEY_DEBUG, false);
    }
}
