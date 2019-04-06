<?php

namespace Vectorface\SnappyRouterTests\Encoder;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Response\Response;

/**
 * A base class for testing the various encoders.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractEncoderTest extends TestCase
{
    /**
     * Tests the encode method of the encoder.
     * @dataProvider encodeProvider
     */
    public function testEncode($expected, $input)
    {
        $encoder = $this->getEncoder();
        $this->assertEquals(
            $expected,
            $encoder->encode(
                new Response($input)
            )
        );
    }

    /**
     * Returns the encoder to be tested.
     * @return \Vectorface\SnappyRouter\Encoder\AbstractEncoder Returns an instance of an encoder.
     */
    abstract public function getEncoder();

    /**
     * A data provider for the testEncode method.
     */
    abstract public function encodeProvider();
}
