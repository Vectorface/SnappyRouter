<?php

namespace Vectorface\SnappyRouterTests\Handler;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Handler\PatternMatchHandler;

/**
 * A test for the PatternMatchHandler class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class PatternMatchHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Demonstrates how to use the PatternMatchHandler class.
     * @test
     */
    public function synopsis()
    {
        $config = array(
            'routes' => array(
                '/user/{name}/{id:[0-9]+}' => array(
                    'get' => function ($routeParams) {
                        return print_r($routeParams, true);
                    }
                ),
                '/anotherRoute' => function () {
                    return false;
                }
            )
        );
        $handler = new PatternMatchHandler($config);
        $this->assertTrue($handler->isAppropriate('/user/asdf/1234', array(), array(), 'GET'));
        $expected = print_r(array('name' => 'asdf', 'id' => 1234), true);
        $this->assertEquals($expected, $handler->performRoute());

        // not a matching pattern
        $this->assertFalse($handler->isAppropriate('/user/1234', array(), array(), 'GET'));

        // matching pattern but invalid HTTP verb
        $this->assertFalse($handler->isAppropriate('/user/asdf/1234', array(), array(), 'POST'));
    }
}
