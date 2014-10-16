<?php

namespace Vectorface\SnappyRouter\Di;

/**
 * An interface for any class wishing to provide a dependency injection
 * mechanism.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface DiInterface
{
    /**
     * Returns the element associated with the specified key.
     * @param string $element The key for the element.
     * @param boolean $useCache An optional flag for whether we can use the
     *        cached version of the element (defaults to true).
     * @return Returns the associated element.
     * @throws \Exception Throws an exception if no element is registered for
     *                    the given key.
     */
    public function get($element, $useCache = true);

    /**
     * Assigns a specific element to the given key. This method will override
     * any previously assigned element for the given key.
     * @param string $element The key for the specified element.
     * @param mixed $value The specified element. This can be an instance of the
     *        element or a callback to be invoked.
     * @return Returns $this.
     */
    public function set($element, $value);

    /**
     * Returns whether or not a given element has been registered.
     * @param string $element The key for the element.
     * @return Returns true if the element is registered and false otherwise.
     */
    public function hasElement($element);

    /**
     * Returns an array of all registered elements (their keys).
     * @return An array of all registered elements (their keys).
     */
    public function allRegisteredElements();
}