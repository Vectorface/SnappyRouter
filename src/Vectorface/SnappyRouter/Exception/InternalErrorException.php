<?php

namespace Vectorface\SnappyRouter\Exception;

use \Exception;
use Vectorface\SnappyRouter\Response\AbstractResponse;

/**
 * A base class for all general server side internal exceptions.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class InternalErrorException extends Exception implements RouterExceptionInterface
{
    /**
     * Returns the associated status code with the exception. By default, most exceptions correspond
     * to a server error (HTTP 500). Override this method if you want your exception to generate a
     * different status code.
     * @return The associated status code.
     */
    public function getAssociatedStatusCode()
    {
        return AbstractResponse::RESPONSE_SERVER_ERROR;
    }
}
