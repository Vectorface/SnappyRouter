<?php

namespace Vectorface\SnappyRouterTests\Encoder;

use Vectorface\SnappyRouter\Encoder\AbstractEncoder;
use Vectorface\SnappyRouter\Encoder\NullEncoder;

/**
 * Tests the NullEncoder class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class NullEncoderTest extends AbstractEncoderTest
{
    /**
     * Returns the encoder to be tested.
     * @return AbstractEncoder Returns an instance of an encoder.
     */
    public function getEncoder()
    {
        return new NullEncoder();
    }

    /**
     * A data provider for the testEncode method.
     */
    public function encodeProvider()
    {
        return array(
            array(
                'test1234',
                'test1234'
            ),
            array(
                '',
                null
            )
        );
    }
}
