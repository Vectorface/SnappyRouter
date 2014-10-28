<?php

namespace Vectorface\SnappyRouterTests;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Di\Di;

class PhpFivePointThreeCompatTest extends PHPUnit_Framework_TestCase
{
    public function testHttpResponseCode()
    {
        $currentDi = Di::getDefault();
        // ensure we have a clean DI component
        Di::setDefault(new Di());
        $this->assertEquals(200, \Vectorface\SnappyRouter\http_response_code());
        Di::setDefault($currentDi);
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
