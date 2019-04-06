<?php

namespace Vectorface\SnappyRouterTests\Exception;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Exception\InternalErrorException;

/**
 * Tests the InternalErrorException class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class InternalErrorExceptionTest extends TestCase
{
    /**
     * An overview of how to use the exception.
     * @test
     */
    public function synopsis()
    {
        $message = 'hello world';
        $exception = new InternalErrorException($message);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(500, $exception->getAssociatedStatusCode());
    }
}
