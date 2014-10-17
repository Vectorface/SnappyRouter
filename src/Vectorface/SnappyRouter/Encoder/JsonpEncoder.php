<?php

namespace Vectorface\SnappyRouter\Encoder;

use Vectorface\SnappyRouter\Response\AbstractResponse;

/**
 * Encodes the response in the JSON-P format.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class JsonpEncoder extends JsonEncoder
{
    /** The method the client is invoking. */
    private $clientMethod;

    /**
     * Constructor for the encoder.
     * @param string $clientMethod The method the client is invoking.
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        $this->clientMethod = 'method';
        if (isset($options['clientMethod']) && is_string($options['clientMethod'])) {
            $this->clientMethod = $options['clientMethod'];
        }
    }

    /**
     * @param AbstractResponse $response The response to be encoded.
     * @return Returns the response encoded in JSON.
     */
    public function encode(AbstractResponse $response)
    {
        $response = parent::encode($response);
        return sprintf('%s(%s);', $this->clientMethod, $response);
    }
}
