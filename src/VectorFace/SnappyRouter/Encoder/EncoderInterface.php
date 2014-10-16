<?php

namespace VectorFace\SnappyRouter\Encoder;

use VectorFace\SnappyRouter\Response\Response;

/**
 * An interface to for all encoders. It is highly recommended to extend the
 * AbstractEncoder class instead of implementing this interface directly.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface EncoderInterface
{
    /**
     * @param Response $response The response to be encoded.
     * @return (string) Returns the response encoded as a string.
     */
    public function encode(Response $response);
}
