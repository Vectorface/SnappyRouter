<?php

namespace Vectorface\SnappyRouter\Config;

/**
 * An interface that must be implemented by any class wishing to provide
 * configuration to the SnappyRouter.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface ConfigInterface
{
    /**
     * Returns the value associated with the given key. An optional default value
     * can be provided and will be returned if no value is associated with the key.
     * @param string $key The key to be used.
     * @param mixed $defaultValue The default value to return if the key currently
     *        has no value associated with it.
     * @return Returns the value associated with the key or the default value if
     *         no value is associated with the key.
     */
    public function get($key, $defaultValue = null);

    /**
     * Sets the current value associated with the given key.
     * @param string $key The key to be set.
     * @param mixed $value The value to be set to the key.
     * @return void
     */
    public function set($key, $value);

    /**
     * Returns an array representation of the whole configuration.
     * @return An array representation of the whole configuration.
     */
    public function toArray();
}
