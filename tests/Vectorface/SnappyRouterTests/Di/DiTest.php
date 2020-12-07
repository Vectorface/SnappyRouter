<?php

namespace Vectorface\SnappyRouterTests\Di;

use Exception;
use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Di\Di;

class DiTest extends TestCase
{
    /**
     * Tests the standard set/get methods of the DI.
     *
     * @dataProvider setAndGetServiceProvider
     * @param string $key
     * @param mixed $element
     * @param string $expected
     * @throws Exception
     */
    public function testSetAndGetService($key, $element, $expected)
    {
        $di = new Di();
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
            [$key],
            $di->allRegisteredElements()
        );
    }

    /**
     * Data provider for the method testSetAndGetService.
     */
    public function setAndGetServiceProvider()
    {
        return [
            [
                'HelloWorldService',
                'Hello world!',
                'Hello world!'
            ],
            [
                'HelloWorldService',
                function() {
                    return 'Hello world!';
                },
                'Hello world!'
            ]
        ];
    }

    /**
     * Tests the methods for getting, setting and clearing the default
     * service provider.
     */
    public function testGetDefaultAndSetDefault()
    {
        Di::clearDefault(); // guard condition
        $di = Di::getDefault(); // get a fresh default
        $this->assertInstanceOf(Di::class, $di);

        Di::setDefault($di);
        $this->assertEquals($di, Di::getDefault());
    }

    /**
     * Tests the exception is thrown when we ask for a service that has not
     * been registered.
     */
    public function testMissingServiceThrowsException()
    {
        $this->setExpectedException(Exception::class, "No element registered for key: TestElement");

        $di = new Di();
        $di->get('TestElement');
    }
}
