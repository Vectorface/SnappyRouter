<?php

namespace Vectorface\SnappyRouter\Config;

use \ArrayAccess;

/**
 * A wrapper object to the SnappyRouter configuration.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class Config implements ArrayAccess, ConfigInterface
{
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
     * @param string $key The key to be checked.
     * @return Returns true if the key exists and false otherwise.
     */
    public function offsetExists($key)
    {
        return isset($this->config[$key]);
    }

    /**
     * Returns the value associated with the key or null if no value exists.
     * @param string $key The key to be fetched.
     * @return Returns the value associated with the key or null if no value exists.
     */
    public function offsetGet($key)
    {
        return $this->offsetExists($key) ? $this->config[$key] : null;
    }

    /**
     * Sets the value associated with the given key.
     * @param string $key The key to be used.
     * @param mixed $value The value to be set.
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (null === $key) {
            throw new \Exception('Config values must contain a key.');
        }
        $this->config[$key] = $value;
    }

    /**
     * Removes the value set to the given key.
     * @param string $key The key to unset.
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->config[$key]);
    }

    /**
     * Returns the value associated with the given key. An optional default value
     * can be provided and will be returned if no value is associated with the key.
     * @param string $key The key to be used.
     * @param mixed $defaultValue The default value to return if the key currently
     *        has no value associated with it.
     * @return Returns the value associated with the key or the default value if
     *         no value is associated with the key.
     */
    public function get($key, $defaultValue = null)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : $defaultValue;
    }

    /**
     * Sets the current value associated with the given key.
     * @param string $key The key to be set.
     * @param mixed $value The value to be set to the key.
     * @return void
     */
    public function set($key, $value)
    {
        return $this->offsetSet($key, $value);
    }

    /**
     * Returns an array representation of the whole configuration.
     * @return An array representation of the whole configuration.
     */
    public function toArray()
    {
        return $this->config;
    }
}
