<?php

namespace Vectorface\SnappyRouter\Di;

/**
 * An interface that exposes the main DI container methods. Implemented by
 * various elements of router for convenience.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface DiProviderInterface
{
    /**
     * Retrieve an element from the DI container.
     * @param string $key The DI key.
     * @param boolean $useCache (optional) An optional indicating whether we
     *        should use the cached version of the element (true by default).
     * @return mixed Returns the DI element mapped to that key.
     */
    public function get($key, $useCache = true);

    /**
     * Sets an element in the DI container for the specified key.
     * @param string $key The DI key.
     * @param mixed  $element The DI element to store.
     * @return Di Returns the Di instance.
     */
    public function set($key, $element);
}
