<?php

namespace Vectorface\SnappyRouter\Di;

use \Exception;

/**
 * A simple store for DI purposes.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class Di implements DiInterface
{
    private $elements; // a cache of instantiated elements
    private $elementMap; // the map between keys and their elements

    private static $instance; // a static instance of this class for static use

    /**
     * Constructor for the class.
     * @param array $elementMap An optional initial set of elements to use.
     */
    public function __construct($elementMap = array())
    {
        $this->elementMap = is_array($elementMap) ? $elementMap : array();
        $this->elements   = array();
    }

    /**
     * Returns the element associated with the specified key.
     * @param string $element The key for the element.
     * @param boolean $useCache An optional flag for whether we can use the
     *        cached version of the element (defaults to true).
     * @return Returns the associated element.
     * @throws \Exception Throws an exception if no element is registered for
     *                    the given key.
     */
    public function get($element, $useCache = true)
    {
        if ($useCache && isset($this->elements[$element])) {
            // return the cached version
            return $this->elements[$element];
        }

        if (isset($this->elementMap[$element])) {
            if (is_callable($this->elementMap[$element])) {
                // if we have callback, invoke it and cache the result
                $this->elements[$element] = call_user_func(
                    $this->elementMap[$element],
                    $this
                );
            } else {
                // otherwise simply cache the result and return it
                $this->elements[$element] = $this->elementMap[$element];
            }
            return $this->elements[$element];
        }

        throw new Exception('No element registered for key: '.$element);
    }

    /**
     * Assigns a specific element to the given key. This method will override
     * any previously assigned element for the given key.
     * @param string $key The key for the specified element.
     * @param mixed $element The specified element. This can be an instance of the
     *        element or a callback to be invoked.
     * @return Returns $this.
     */
    public function set($key, $element)
    {
        // clear the cached element
        unset($this->elements[$key]);
        $this->elementMap[$key] = $element;
        return $this;
    }

    /**
     * Returns whether or not a given element has been registered.
     * @param string $element The key for the element.
     * @return Returns true if the element is registered and false otherwise.
     */
    public function hasElement($element)
    {
        return isset($this->elementMap[$element]);
    }

    /**
     * Returns an array of all registered keys.
     * @return An array of all registered keys.
     */
    public function allRegisteredElements()
    {
        return array_keys($this->elementMap);
    }

    /**
     * Returns the current default DI instance.
     * @return Di The current default DI instance.
     */
    public static function getDefault()
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * Sets the current default DI instance..
     * @param DiInterface $instance An instance of DI.
     * @return Di Returns the new default DI instance.
     */
    public static function setDefault(DiInterface $instance)
    {
        self::$instance = $instance;
        return self::$instance;
    }

    /**
     * Clears the current default DI instance.
     */
    public static function clearDefault()
    {
        self::$instance = null;
    }
}
