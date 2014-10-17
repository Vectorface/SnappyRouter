<?php

namespace Vectorface\SnappyRouterTests\Encoder;

use Vectorface\SnappyRouter\Encoder\JsonpEncoder;

/**
 * Tests the JsonpEncoder class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class JsonpEncoderTest extends AbstractEncoderTest
{
    /**
     * Returns the encoder to be tested.
     * @return EncoderInterface Returns an instance of an encoder.
     */
    public function getEncoder()
    {
        $options = array(
            'clientMethod' => 'doSomething'
        );
        return new JsonpEncoder($options);
    }

    /**
     * A data provider for the testEncode method.
     */
    public function encodeProvider()
    {
        return array(
            array(
                'doSomething("test1234");',
                'test1234'
            )
        );
    }
}
