<?php

namespace Vectorface\SnappyRouter\Exception;

use Exception;
use Vectorface\SnappyRouter\Response\AbstractResponse;

/**
 * An exception indicating that authentication is required and has failed or has
 * not yet been provided
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author J. Anderson <janderson@vectorface.com>
 * @author Dan Bruce   <dbruce@vectorface.com>
 */
class UnauthorizedException extends Exception implements RouterExceptionInterface
{
    /**
     * Gets the status code that corresponds to this exception. This is usually
     * an HTTP status code.
     *
     * @return int The status code associated with this exception.
     */
    public function getAssociatedStatusCode()
    {
        return AbstractResponse::RESPONSE_UNAUTHORIZED;
    }
}
