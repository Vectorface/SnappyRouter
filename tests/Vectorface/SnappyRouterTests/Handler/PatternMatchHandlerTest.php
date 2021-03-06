<?php

namespace Vectorface\SnappyRouterTests\Handler;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Exception\PluginException;
use Vectorface\SnappyRouter\Handler\PatternMatchHandler;

/**
 * A test for the PatternMatchHandler class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class PatternMatchHandlerTest extends TestCase
{
    /**
     * Demonstrates how to use the PatternMatchHandler class.
     *
     * @throws PluginException
     */
    public function testSynopsis()
    {
        $config = [
            'routes' => [
                '/user/{name}/{id:[0-9]+}' => [
                    'get' => function($routeParams) {
                        return print_r($routeParams, true);
                    }
                ],
                '/anotherRoute' => function() {
                    return false;
                }
            ]
        ];
        $handler = new PatternMatchHandler($config);
        $this->assertTrue($handler->isAppropriate('/user/asdf/1234', [], [], 'GET'));
        $expected = print_r(['name' => 'asdf', 'id' => 1234], true);
        $this->assertEquals($expected, $handler->performRoute());

        // not a matching pattern
        $this->assertFalse($handler->isAppropriate('/user/1234', [], [], 'GET'));

        // matching pattern but invalid HTTP verb
        $this->assertFalse($handler->isAppropriate('/user/asdf/1234', [], [], 'POST'));
    }

    /**
     * Tests that the cached route handler works as well.
     *
     * @throws PluginException
     */
    public function testCachedRouteHandler()
    {
        $cacheFile = __DIR__.'/routes.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
        $config = [
            'routes' => [
                '/user/{name}/{id:[0-9]+}' => [
                    'get' => function($routeParams) {
                        return print_r($routeParams, true);
                    }
                ],
                '/anotherRoute' => function() {
                    return false;
                }
            ],
            'routeCache' => [
                'cacheFile' => $cacheFile
            ]
        ];

        $handler = new PatternMatchHandler($config);
        $this->assertTrue($handler->isAppropriate('/user/asdf/1234', [], [], 'GET'));
        $this->assertNotEmpty(file_get_contents($cacheFile));
        unlink($cacheFile);
    }

    /**
     * Tests that the getRequest() method returns null.
     *
     * @throws PluginException
     */
    public function testGetRequest()
    {
        $config = [
            'routes' => [
                '/testRoute' => function() {
                    return false;
                }
            ]
        ];
        $handler = new PatternMatchHandler($config);
        $this->assertTrue($handler->isAppropriate('/testRoute', [], [], 'GET'));
        $this->assertNull($handler->getRequest());
    }
}
