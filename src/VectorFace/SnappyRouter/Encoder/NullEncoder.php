<?php

namespace Vectorface\SnappyRouter\Encoder;

use Vectorface\SnappyRouter\Response\Response;

/**
 * An encoder that simply returns the response object directly.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class NullEncoder extends AbstractEncoder
{
    /**
     * @param Response $response The response to be encoded.
     * @return (string) Returns the response encoded as a string.
     */
    public function encode(Response $response)
    {
        return $response->getResponseObject();
    }
}
