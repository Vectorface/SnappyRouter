<?php

namespace Vectorface\SnappyRouterTests;

use \PHPUnit_Framework_TestCase;

class PhpFivePointThreeCompatTest extends PHPUnit_Framework_TestCase
{
    public function testHttpResponseCode()
    {
        $this->assertEquals(200, \Vectorface\SnappyRouter\http_response_code());
    }

    /**
     * Tests that an invalid status code throws an exception.
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid response code: 9999
     */
    public function testInvalidStatusCodeThrowsException()
    {
        \Vectorface\SnappyRouter\http_response_code(9999);
    }
}
