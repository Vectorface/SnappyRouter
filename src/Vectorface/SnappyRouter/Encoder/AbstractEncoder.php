<?php

namespace Vectorface\SnappyRouter\Encoder;

/**
 * An abstract base class for all encoders. Extend this class to implement a
 * custom encoder.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractEncoder implements EncoderInterface
{
    // an array of options for the encoder
    private $options;

    /**
     * Constructor for the encoder.
     * @param array $options An array of encoder options.
     */
    public function __construct($options = [])
    {
        $this->options = (array)$options;
    }
}
