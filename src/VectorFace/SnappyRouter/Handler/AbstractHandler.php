<?php

namespace VectorFace\SnappyRouter\Handler;

use VectorFace\SnappyRouter\Di\Di;
use VectorFace\SnappyRouter\Di\DiProvider;

/**
 * The base class for all handlers.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractHandler implements DiProvider
{
    /** an array of handler-specific options */
    protected $options;

    /**
     * Constructor for the class.
     * @param array $options An array of options for the plugin.
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Retrieve an element from the DI container.
     * @param string $key The DI key.
     * @param boolean $useCache (optional) An optional indicating whether we
     *        should use the cached version of the element (true by default).
     * @return Returns the DI element mapped to that key.
     */
    public function get($key, $useCache = true)
    {
        return Di::getDefault()->get($key, $useCache);
    }

    /**
     * Sets an element in the DI container for the specified key.
     * @param string $key The DI key.
     * @param mixed  $element The DI element to store.
     * @return Di Returns the Di instance.
     */
    public function set($key, $element)
    {
        return Di::getDefault()->set($key, $element);
    }

    /**
     * Performs the actual routing.
     * @return Returns the result of the route.
     */
    abstract public function performRoute();
}
