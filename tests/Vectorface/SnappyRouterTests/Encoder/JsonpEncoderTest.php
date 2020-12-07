<?php

namespace Vectorface\SnappyRouterTests\Encoder;

use Exception;
use Vectorface\SnappyRouter\Encoder\AbstractEncoder;
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
     *
     * @return AbstractEncoder Returns an instance of an encoder.
     * @throws Exception
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

    /**
     * Tests that an exception is thrown if the client method is missing from
     * the options.
     */
    public function testMissingClientMethodThrowsException()
    {
        $this->setExpectedException(Exception::class, "Client method missing from plugin options.");

        new JsonpEncoder(array());
    }
}
