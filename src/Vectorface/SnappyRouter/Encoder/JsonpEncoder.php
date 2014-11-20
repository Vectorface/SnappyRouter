<?php

namespace Vectorface\SnappyRouter\Encoder;

use \Exception;
use Vectorface\SnappyRouter\Response\AbstractResponse;

/**
 * Encodes the response in the JSON-P format.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class JsonpEncoder extends JsonEncoder
{
    /** the config key for the client side method to invoke */
    const KEY_CLIENT_METHOD = 'clientMethod';

    /** The method the client is invoking. */
    private $clientMethod;

    /**
     * Constructor for the encoder.
     * @param array $options (optional) The array of plugin options.
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        if (!isset($options[self::KEY_CLIENT_METHOD])) {
            throw new Exception('Client method missing from plugin options.');
        }
        $this->clientMethod = (string)$options[self::KEY_CLIENT_METHOD];
    }

    /**
     * @param AbstractResponse $response The response to be encoded.
     * @return Returns the response encoded in JSON.
     */
    public function encode(AbstractResponse $response)
    {
        return sprintf(
            '%s(%s);',
            $this->clientMethod,
            parent::encode($response)
        );
    }
}
