<?php

namespace Vectorface\SnappyRouterTests\Handler;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\SnappyRouter;
use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Encoder\NullEncoder;
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
        $options = array(
            ControllerHandler::KEY_BASE_PATH => '/',
            Config::KEY_SERVICES => array(
                'ControllerController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController',
                'IndexController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
            )
        );
        $handler = new ControllerHandler($options);

        $path = '/controller/default/param1/param2';
        $query = array('id' => '1234');
        $post  = array('key' => 'value');

        // a route with parameters
        $this->assertTrue($handler->isAppropriate($path, $query, $post, 'POST'));
        $request = $handler->getRequest();
        $this->assertEquals('ControllerController', $request->getController());
        $this->assertEquals('defaultAction', $request->getAction());
        $this->assertEquals('POST', $request->getVerb());
        $this->assertFalse($request->isGet());
        $this->assertTrue($request->isPost());

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
     * Tests the handler returns false for a route to an unknown controller.
     */
    public function testRouteToNonExistantController()
    {
        $options = array();
        $handler = new ControllerHandler($options);

        $path = '/test/test';
        $this->assertFalse($handler->isAppropriate($path, array(), array(), 'GET'));
    }

    /**
     * Tests that an exception is thrown if we try to route to a controller
     * action that does not exist.
     * @expectedException Vectorface\SnappyRouter\Exception\ResourceNotFoundException
     * @expectedExceptionMessage TestController does not have method notexistsAction
     */
    public function testRouteToNonExistantControllerAction()
    {
        $options = array(
            Config::KEY_SERVICES => array(
                'TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
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
            Config::KEY_PLUGINS => array(
                'TestPlugin' => array()
            )
        );
        new ControllerHandler($options);
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
            Config::KEY_PLUGINS => array(
                'TestPlugin' => array(
                    'class' => 'Vectorface\\SnappyRouter\\Plugin\\NonExistantPlugin'
                )
            )
        );
        new ControllerHandler($options);
    }

    /**
     * Tests that an invalid view configuration throws an exception.
     * @expectedException Vectorface\SnappyRouter\Exception\InternalErrorException
     * @expectedExceptionMessage View environment missing views path.
     */
    public function testInvalidViewConfiguration()
    {
        $options = array(
            ControllerHandler::KEY_BASE_PATH => '/',
            Config::KEY_SERVICES => array(
                'ControllerController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController',
            ),
            ControllerHandler::KEY_VIEWS => array()
        );
        $handler = new ControllerHandler($options);
        $handler->isAppropriate('/controller', array(), array(), 'GET');
    }

    /**
     * Tests that the default action of a controller is to render a default
     * view from the view engine.
     */
    public function testRenderDefaultView()
    {
        $routerOptions = array(
            Config::KEY_DI => 'Vectorface\\SnappyRouter\\Di\\Di',
            Config::KEY_HANDLERS => array(
                'ControllerHandler' => array(
                    Config::KEY_CLASS => 'Vectorface\\SnappyRouter\\Handler\\ControllerHandler',
                    Config::KEY_OPTIONS => array(
                        Config::KEY_SERVICES => array(
                            'TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
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

    /**
     * Tests that when an action returns a string, the string is rendered
     * without going through the view engine.
     */
    public function testActionReturnsString()
    {
        $options = array(
            Config::KEY_SERVICES => array(
                'TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
            ),
            ControllerHandler::KEY_VIEWS => array(
                ControllerHandler::KEY_VIEWS_PATH => __DIR__.'/../Controller/Views'
            )
        );
        $handler = new ControllerHandler($options);
        $this->assertTrue($handler->isAppropriate('/test/test', array(), array(), 'GET'));
        $this->assertEquals('This is a test service.', $handler->performRoute());
    }

    /**
     * Tests that when an action returns an array, the twig view is rendered
     * with the array values as the variables in the view.
     */
    public function testActionReturnsArray()
    {
        $options = array(
            Config::KEY_SERVICES => array(
                'TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
            ),
            ControllerHandler::KEY_VIEWS => array(
                ControllerHandler::KEY_VIEWS_PATH => __DIR__.'/../Controller/Views'
            )
        );
        $handler = new ControllerHandler($options);
        $this->assertTrue($handler->isAppropriate('/test/array', array(), array(), 'GET'));
        $this->assertEquals('This is a test service.', $handler->performRoute());
    }

    /**
     * Tests that an action can render a different view that its default.
     */
    public function testActionRendersNonDefaultView()
    {
        $options = array(
            Config::KEY_SERVICES => array(
                'TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
            ),
            ControllerHandler::KEY_VIEWS => array(
                ControllerHandler::KEY_VIEWS_PATH => __DIR__.'/../Controller/Views'
            )
        );
        $handler = new ControllerHandler($options);
        $this->assertTrue($handler->isAppropriate('/test/otherView', array(), array(), 'GET'));
        $this->assertEquals('This is a test service.', $handler->performRoute());
    }

    /**
     * Tests that an exception is thrown if we "renderView" with a NullEncoder
     * @expectedException Exception
     * @expectedExceptionMessage The current encoder does not support the render view method.
     */
    public function testExceptionForNullEncoderRenderView()
    {
        $options = array(
            Config::KEY_SERVICES => array(
                'TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
            ),
            ControllerHandler::KEY_VIEWS => array(
                ControllerHandler::KEY_VIEWS_PATH => __DIR__.'/../Controller/Views'
            )
        );
        $handler = new ControllerHandler($options);
        $this->assertTrue($handler->isAppropriate('/test/otherView', array(), array(), 'GET'));
        $handler->setEncoder(new NullEncoder());
        $handler->performRoute();
    }

    /**
     * Tests that we can use namespace provisioning to retrieve a controller.
     */
    public function testNamespaceProvisioning()
    {
        $options = array(
            Config::KEY_NAMESPACES => array(
                'Vectorface\\SnappyRouterTests\\Controller'
            ),
            ControllerHandler::KEY_VIEWS => array(
                ControllerHandler::KEY_VIEWS_PATH => __DIR__.'/../Controller/Views'
            )
        );
        $handler = new ControllerHandler($options);
        $this->assertTrue($handler->isAppropriate('/testDummy/test', array(), array(), 'GET'));
        $this->assertEquals('This is a test service.', $handler->performRoute());
    }

    /**
     * Tests that we can use folder provisioning to retrieve a controller.
     */
    public function testFolderProvisioning()
    {
        $options = array(
            Config::KEY_FOLDERS => array(
                realpath(__DIR__.'/../Controller')
            ),
            ControllerHandler::KEY_VIEWS => array(
                ControllerHandler::KEY_VIEWS_PATH => __DIR__.'/../Controller/Views'
            )
        );
        $handler = new ControllerHandler($options);
        $this->assertTrue($handler->isAppropriate('/nonNamespaced/test', array(), array(), 'GET'));
        $this->assertEquals('This is a test string.', $handler->performRoute());
    }
}
