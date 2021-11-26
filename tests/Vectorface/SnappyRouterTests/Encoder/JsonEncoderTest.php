<?php

namespace Vectorface\SnappyRouterTests\Encoder;

use stdClass;
use Vectorface\SnappyRouter\Encoder\AbstractEncoder;
use Vectorface\SnappyRouter\Encoder\JsonEncoder;
use Vectorface\SnappyRouter\Exception\EncoderException;
use Vectorface\SnappyRouter\Response\Response;

/**
 * Tests the JsonEncoder class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class JsonEncoderTest extends AbstractEncoderTest
{
    /**
     * Returns the encoder to be tested.
     * @return AbstractEncoder Returns an instance of an encoder.
     */
    public function getEncoder()
    {
        return new JsonEncoder();
    }

    /**
     * A data provider for the testEncode method.
     */
    public function encodeProvider()
    {
        $testObject = new stdClass();
        $testObject->id = 1234;
        return [
            [
                '"test1234"',
                'test1234'
            ],
            [
                '{"id":1234}',
                ['id' => 1234]
            ],
            [
                '{"id":1234}',
                $testObject,
            ],
            [
                'null',
                null
            ],
            [
                '"testSerialize"',
                $this
            ]
        ];
    }

    /**
     * Tests that we get an exception if we attempt to encode something that
     * is not serializable as JSON.
     *
     * @throws EncoderException
     */
    public function testNonSerializableEncode()
    {
        $this->expectException(EncoderException::class);
        $this->expectExceptionMessage("Unable to encode response as JSON.");

        $encoder = new JsonEncoder();
        $resource = fopen(__FILE__, 'r'); // resources can't be serialized
        $encoder->encode(new Response($resource));
    }

    public function jsonSerialize()
    {
        return 'testSerialize';
    }
}
