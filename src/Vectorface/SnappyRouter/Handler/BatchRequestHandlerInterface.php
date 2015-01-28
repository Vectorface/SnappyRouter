<?php

namespace Vectorface\SnappyRouter\Handler;

/**
 * An interface for handler that deal with batch requests.
 *
 * @copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface BatchRequestHandlerInterface
{
    /**
     * Returns an array of batched requests.
     * @return An array of batched requests.
     */
    public function getRequests();
}
