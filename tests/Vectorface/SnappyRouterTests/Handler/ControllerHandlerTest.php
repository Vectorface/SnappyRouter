<?php

namespace Vectorface\SnappyRouterTests\Handler;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\SnappyRouter;
use Vectorface\SnappyRouter\Config\Config;
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
                'TestController' => 'Vectorface\SnappyRouterTests\Controller\TestDummyController'
            )
        );
        $handler = new ControllerHandler($options);

        $path = '/test/notExists';
        $this->assertTrue($handler->isAppropriate($path, array(), array(), 'GET'));

        $handler->performRoute();
    }

    /**
     * Tests that an exception is thrown if the handler has a plugin missing
     * the class field.
     * @expectedException Vectorface\SnappyRouter\Exception\PluginException
     * @expectedExceptionMessage Invalid or missing class for plugin TestPlugin
     */
    public function testMissingClassOnPlugin()
    {
        $options = array(
            AbstractHandler::KEY_PLUGINS => array(
                'TestPlugin' => array()
            )
        );
        $handler = new ControllerHandler($options);
    }

    /**
     * Tests that an exception is thrown if the handler lists a non-existant
     * plugin class.
     * @expectedException Vectorface\SnappyRouter\Exception\PluginException
     * @expectedExceptionMessage Invalid or missing class for plugin TestPlugin
     */
    public function testInvalidClassOnPlugin()
    {
        $options = array(
            AbstractHandler::KEY_PLUGINS => array(
                'TestPlugin' => array(
                    'class' => 'Vectorface\SnappyRouter\Plugin\NonExistantPlugin'
                )
            )
        );
        $handler = new ControllerHandler($options);
    }

    /**
     * Tests that the default action of a controller is to render a default
     * view from the view engine.
     */
    public function testRenderDefaultView()
    {
        $routerOptions = array(
            SnappyRouter::KEY_DI => 'Vectorface\SnappyRouter\Di\Di',
            SnappyRouter::KEY_HANDLERS => array(
                'ControllerHandler' => array(
                    AbstractHandler::KEY_CLASS => 'Vectorface\SnappyRouter\Handler\ControllerHandler',
                    AbstractHandler::KEY_OPTIONS => array(
                        AbstractHandler::KEY_SERVICES => array(
                            'TestController' => 'Vectorface\SnappyRouterTests\Controller\TestDummyController'
                        ),
                        ControllerHandler::KEY_VIEWS => array(
                            ControllerHandler::KEY_VIEWS_PATH => __DIR__.'/../Controller/Views'
                        )
                    )
                )
            )
        );
        $router = new SnappyRouter(new Config($routerOptions));

        $path = '/test/default';
        $response = $router->handleHttpRoute($path, array(), array(), 'GET');
        $expected = file_get_contents(__DIR__.'/../Controller/Views/test/default.twig');
        $this->assertEquals($expected, $response);
    }
}
