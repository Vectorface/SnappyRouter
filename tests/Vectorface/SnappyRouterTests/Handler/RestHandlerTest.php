<?php

namespace Vectorface\SnappyRouterTests\Handler;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Handler\RestHandler;

/**
 * A test for the RestHandler class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class RestHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * An overview of how to use the RestHandler class.
     * @test
     */
    public function synopsis()
    {
        $options = array(
            RestHandler::KEY_BASE_PATH => '/',
            Config::KEY_CONTROLLERS => array(
                'TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
            )
        );
        $handler = new RestHandler($options);
        $this->assertTrue($handler->isAppropriate('/v1/test', array(), array(), 'GET'));
        $result = json_decode($handler->performRoute());
        $this->assertTrue(empty($result));
    }

    /**
     * Tests the possible paths that could be handled by the RestHandler.
     * @dataProvider restPathsProvider
     */
    public function testRestHandlerHandlesPath($expected, $path)
    {
        $options = array(
            RestHandler::KEY_BASE_PATH => '/',
            Config::KEY_CONTROLLERS => array(
                'TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
            )
        );
        $handler = new RestHandler($options);
        $this->assertEquals($expected, $handler->isAppropriate($path, array(), array(), 'GET'));
    }

    /**
     * The data provider for testing various paths against the RestHandler.
     */
    public function restPathsProvider()
    {
        return array(
            array(
                true,
                '/v1/test'
            ),
            array(
                true,
                '/v1.2/test'
            ),
            array(
                true,
                '/v1.2/Test'
            ),
            array(
                true,
                '/v1.2/test/1234'
            ),
            array(
                true,
                '/v1.2/test/someAction'
            ),
            array(
                true,
                '/v1.2/test/1234/someAction'
            ),
            array(
                false,
                '/v1.2'
            ),
            array(
                false,
                '/v1.2/noController'
            ),
            array(
                false,
                '/v1.2/1234/5678'
            )
        );
    }
}
