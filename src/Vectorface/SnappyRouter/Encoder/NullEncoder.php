<?php

namespace Vectorface\SnappyRouter\Encoder;

use Vectorface\SnappyRouter\Response\AbstractResponse;

/**
 * An encoder that simply returns the response object directly.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class NullEncoder extends AbstractEncoder
{
    /**
     * @param AbstractResponse $response The response to be encoded.
     * @return string Returns the response encoded as a string.
     */
    public function encode(AbstractResponse $response)
    {
        if (is_string($response->getResponseObject())) {
            return $response->getResponseObject();
        } else {
            return '';
        }
    }
}
