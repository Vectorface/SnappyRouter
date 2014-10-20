<?php

namespace Vectorface\SnappyRouterTests\Di;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Di\Di;

class DiTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests the standard set/get methods of the DI.
     * @dataProvider setAndGetServiceProvider
     */
    public function testSetAndGetService($key, $element, $expected)
    {
        $di = new DI();
        $di->set($key, $element);
        // check that we get back what we expect
        $this->assertEquals(
            $expected,
            $di->get($key, false)
        );
        // check we get the same value if we use the cache
        $this->assertEquals(
            $expected,
            $di->get($key, true)
        );
        // and again if we force a "no cache" hit
        $this->assertEquals(
            $expected,
            $di->get($key, false)
        );
        $this->assertTrue($di->hasElement($key));
        $this->assertEquals(
            array($key),
            $di->allRegisteredElements()
        );
    }

    /**
     * Data provider for the method testSetAndGetService.
     */
    public function setAndGetServiceProvider()
    {
        return array(
            array(
                'HelloWorldService',
                'Hello world!',
                'Hello world!'
            ),
            array(
                'HelloWorldService',
                function () {
                    return 'Hello world!';
                },
                'Hello world!'
            )
        );
    }

    /**
     * Tests the methods for getting, setting and clearing the default
     * service provider.
     */
    public function testGetDefaultAndSetDefault()
    {
        DI::clearDefault(); // guard condition
        $di = DI::getDefault(); // get a fresh default
        $this->assertInstanceOf('Vectorface\SnappyRouter\Di\Di', $di);

        DI::setDefault($di);
        $this->assertEquals($di, DI::getDefault());
    }

    /**
     * Tests the exception is thrown when we ask for a service that has not
     * been registered.
     * @expectedException \Exception
     * @expectedExceptionMessage No element registered for key: TestElement
     */
    public function testMissingServiceThrowsException()
    {
        $di = new DI();
        $di->get('TestElement');
    }
}
