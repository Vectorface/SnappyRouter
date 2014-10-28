<?php

namespace Vectorface\SnappyRouter\Exception;

use \Exception;

use Vectorface\SnappyRouter\Response\AbstractResponse;

/**
 * An exception to be thrown to generate a "404" like response.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class ResourceNotFoundException extends Exception implements RouterExceptionInterface
{
    /**
     * Returns the associated status code with the exception.
     * @return int The associated status code.
     */
    public function getAssociatedStatusCode()
    {
        return AbstractResponse::RESPONSE_NOT_FOUND;
    }
}
