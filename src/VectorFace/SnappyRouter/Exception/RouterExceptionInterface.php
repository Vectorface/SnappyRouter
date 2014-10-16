<?php

namespace Vectorface\SnappyRouter\Exception;

/**
 * An interface for all router exceptions.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface RouterExceptionInterface
{
    /**
     * Returns the associated status code with the exception.
     * @return int The associated status code.
     */
    public function getAssociatedStatusCode();
}
