<?php

namespace Vectorface\SnappyRouterTests\Exception;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Exception\MethodNotAllowedException;

/**
 * Tests the MethodNotAllowedException class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class MethodNotAllowedExceptionTest extends TestCase
{
    /**
     * An overview of how the class works.
     */
    public function testSynopsis()
    {
        $exception = new MethodNotAllowedException(
            'Cannot use GET',
            ['POST']
        );
        $this->assertEquals(405, $exception->getAssociatedStatusCode());
        try {
            throw $exception;
        } catch (MethodNotAllowedException $e) {
            $this->assertEquals('Cannot use GET', $e->getMessage());
        }
    }
}
