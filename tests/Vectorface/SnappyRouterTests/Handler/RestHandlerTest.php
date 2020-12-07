<?php

namespace Vectorface\SnappyRouterTests\Handler;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Exception\InternalErrorException;
use Vectorface\SnappyRouter\Exception\PluginException;
use Vectorface\SnappyRouter\Exception\ResourceNotFoundException;
use Vectorface\SnappyRouter\Handler\RestHandler;
use Vectorface\SnappyRouterTests\Controller\TestDummyController;

/**
 * A test for the RestHandler class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class RestHandlerTest extends TestCase
{
    /**
     * An overview of how to use the RestHandler class.
     *
     * @throws InternalErrorException|PluginException|ResourceNotFoundException
     */
    public function testSynopsis()
    {
        $options = [
            RestHandler::KEY_BASE_PATH => '/',
            Config::KEY_CONTROLLERS    => [
                'TestController' => TestDummyController::class
            ]
        ];
        $handler = new RestHandler($options);
        $this->assertTrue($handler->isAppropriate('/v1/test', [], [], 'GET'));
        $result = json_decode($handler->performRoute());
        $this->assertTrue(empty($result));
    }

    /**
     * Tests the possible paths that could be handled by the RestHandler.
     *
     * @dataProvider restPathsProvider
     * @param bool $expected
     * @param string $path
     * @throws InternalErrorException
     * @throws PluginException
     */
    public function testRestHandlerHandlesPath($expected, $path)
    {
        $options = [
            RestHandler::KEY_BASE_PATH => '/',
            Config::KEY_CONTROLLERS    => [
                'TestController' => TestDummyController::class,
            ]
        ];
        $handler = new RestHandler($options);
        $this->assertEquals($expected, $handler->isAppropriate($path, [], [], 'GET'));
    }

    /**
     * The data provider for testing various paths against the RestHandler.
     */
    public function restPathsProvider()
    {
        return [
            [
                true,
                '/v1/test'
            ],
            [
                true,
                '/v1.2/test'
            ],
            [
                true,
                '/v1.2/Test'
            ],
            [
                true,
                '/v1.2/test/1234'
            ],
            [
                true,
                '/v1.2/test/someAction'
            ],
            [
                true,
                '/v1.2/test/1234/someAction'
            ],
            [
                false,
                '/v1.2'
            ],
            [
                true,
                '/v1.2/noController'
            ],
            [
                false,
                '/v1.2/1234/5678'
            ]
        ];
    }
}
