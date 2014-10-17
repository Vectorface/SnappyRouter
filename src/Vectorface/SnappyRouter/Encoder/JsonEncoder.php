<?php

namespace Vectorface\SnappyRouter\Encoder;

use Vectorface\SnappyRouter\Response\AbstractResponse;
use Vectorface\SnappyRouter\Exception\EncoderException;

/**
 * Encodes the response in the JSON format.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class JsonEncoder extends AbstractEncoder
{
    /**
     * @param AbstractResponse $response The response to be encoded.
     * @return (string) Returns the response encoded as a string.
     */
    public function encode(AbstractResponse $response)
    {
        $responseObject = $response->getResponseObject();
        if (null === $responseObject || is_array($responseObject) || is_scalar($responseObject)) {
            return json_encode($responseObject);
        } elseif (is_object($responseObject)) {
            if (method_exists($responseObject, 'jsonSerialize')) {
                return json_encode($responseObject->jsonSerialize());
            } else {
                return json_encode(get_object_vars($responseObject));
            }
        } else {
            throw new EncoderException('Unable to encode response as JSON.');
        }
    }
}
