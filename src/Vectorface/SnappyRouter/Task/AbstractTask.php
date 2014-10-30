<?php

namespace Vectorface\SnappyRouter\Task;

use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Di\DiProviderInterface;

/**
 * An abstract base task class to be extended when writing custom cli tasks.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class AbstractTask implements DiProviderInterface
{
    // an array of cli handler options
    private $options;

    /**
     * Initializes the task with an array of options.
     * @param array $options The array of options.
     * @return AbstractTask Returns $this.
     */
    public function init($options)
    {
        $this->setOptions($options);
        return $this;
    }

    /**
     * Returns the current set of options.
     * @return array The current set of options.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the current set of options.
     * @param array $options The set of options.
     * @return AbstractTask Returns $this.
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
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
}
