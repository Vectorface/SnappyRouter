<?php

namespace VectorFace\SnappyRouter\Di;

/**
 * A trait that indicates the class provides the DI layer through the standard
 * expected methods.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
trait DiProvider
{
    /**
     * Helper method to fetch DI dependencies.
     * @param string $key The DI key.
     * @param boolean $useCache (optional) An optional indicating whether we
     *        should use the cached version of the element (true by default).
     * @return Returns the DI element mapped to that key.
     */
    protected function get($key, $useCache = true)
    {
        return DI::getDefault()->get($key, $useCache);
    }
}
