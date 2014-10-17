<?php

namespace Vectorface\SnappyRouterTests\Handler;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Handler\AbstractHandler;
use Vectorface\SnappyRouter\Handler\ControllerHandler;

/**
 * Tests the ControllerHandler.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class ControllerHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * An overview of how to use the ControllerHandler.
     * @test
     */
    public function synopsis()
    {
        $options = array();
        $handler = new ControllerHandler($options);

        $path = '/controller/action/param1/param2';
        $query = array('id' => '1234');
        $post  = array('key' => 'value');

        // a route with parameters
        $this->assertTrue($handler->isAppropriate($path, $query, $post, 'POST'));
        $request = $handler->getRequest();
        $this->assertEquals('ControllerController', $request->getController());
        $this->assertEquals('actionAction', $request->getAction());
        $this->assertEquals('POST', $request->getVerb());

        // a route with only an action, no params
        $this->assertTrue($handler->isAppropriate('/controller/action/', $query, $post, 'POST'));

        // a route with only a controller, no action nor params
        $this->assertTrue($handler->isAppropriate('/controller/', $query, $post, 'POST'));

        // a route with nothing
        $this->assertTrue($handler->isAppropriate('/', $query, $post, 'POST'));

        // encoder and decoder methods
        $this->assertEquals(
            $handler->getEncoder(),
            $handler->setEncoder($handler->getEncoder())->getEncoder()
        );

        // test the DI integration in the handler
        $handler->set('MyKey', 'myValue');
        $this->assertEquals('myValue', $handler->get('MyKey'));
    }

    /**
     * Tests that an exception is thrown if we try to route to a controller
     * action that does not exist.
     * @expectedException Vectorface\SnappyRouter\Exception\HandlerException
     * @expectedExceptionMessage TestController does not have method notexistsAction
     */
    public function testRouteToNonExistantControllerAction()
    {
        $options = array(
            AbstractHandler::KEY_SERVICES => array(
                'TestController' => 'VectorFace\SnappyRouterTests\Controller\TestDummyController'
            )
        );
        $handler = new ControllerHandler($options);

        $path = '/test/notExists';
        $this->assertTrue($handler->isAppropriate($path, array(), array(), 'GET'));

        $handler->performRoute();
    }
}
