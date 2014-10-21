<?php

namespace Vectorface\SnappyRouter\Exception;

use \Exception;
use Vectorface\SnappyRouter\Response\AbstractResponse;

/**
 * An exception indicated access has been denied to the resource.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class AccessDeniedException extends Exception implements RouterExceptionInterface
{
    /**
     * Returns the associated status code with the exception.
     * @return int The associated status code.
     */
    public function getAssociatedStatusCode()
    {
        return AbstractResponse::RESPONSE_FORBIDDEN;
    }
}
