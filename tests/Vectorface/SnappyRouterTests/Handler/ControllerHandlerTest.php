<?php

namespace Vectorface\SnappyRouterTests\Handler;

use Exception;
use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Exception\InternalErrorException;
use Vectorface\SnappyRouter\Exception\PluginException;
use Vectorface\SnappyRouter\Exception\ResourceNotFoundException;
use Vectorface\SnappyRouter\SnappyRouter;
use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Encoder\NullEncoder;
use Vectorface\SnappyRouter\Handler\ControllerHandler;
use Vectorface\SnappyRouterTests\Controller\TestDummyController;

/**
 * Tests the ControllerHandler.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class ControllerHandlerTest extends TestCase
{
    /**
     * An overview of how to use the ControllerHandler.
     *
     * @throws PluginException|InternalErrorException
     * @throws Exception
     */
    public function testSynopsis()
    {
        $options = array(
            ControllerHandler::KEY_BASE_PATH => '/',
            Config::KEY_CONTROLLERS => array(
                'ControllerController' => TestDummyController::class,
                'IndexController' => TestDummyController::class,
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
     *
     * @throws InternalErrorException|PluginException|ResourceNotFoundException
     */
    public function testRouteToNonExistentController()
    {
        $this->setExpectedException(ResourceNotFoundException::class, 'No such controller found "TestController".');

        $options = array();
        $handler = new ControllerHandler($options);

        $path = '/test/test';
        $this->assertTrue($handler->isAppropriate($path, array(), array(), 'GET'));
        $handler->performRoute();
    }

    /**
     * Tests that an exception is thrown if we try to route to a controller
     * action that does not exist.
     *
     * @throws InternalErrorException|PluginException|ResourceNotFoundException
     */
    public function testRouteToNonExistentControllerAction()
    {
        $this->setExpectedException(ResourceNotFoundException::class, 'TestController does not have method notexistsAction');

        $options = array(
            Config::KEY_CONTROLLERS => array(
                'TestController' => TestDummyController::class
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
     *
     * @throws PluginException
     */
    public function testMissingClassOnPlugin()
    {
        $this->setExpectedException(PluginException::class, 'Invalid or missing class for plugin TestPlugin');

        $options = array(
            Config::KEY_PLUGINS => array(
                'TestPlugin' => array()
            )
        );
        return new ControllerHandler($options);
    }

    /**
     * Tests that an exception is thrown if the handler lists a non-existent
     * plugin class.
     *
     * @throws PluginException
     */
    public function testInvalidClassOnPlugin()
    {
        $this->setExpectedException(PluginException::class, 'Invalid or missing class for plugin TestPlugin');

        $options = array(
            Config::KEY_PLUGINS => array(
                'TestPlugin' => array(
                    'class' => 'Vectorface\\SnappyRouter\\Plugin\\NonExistentPlugin'
                )
            )
        );
        return new ControllerHandler($options);
    }

    /**
     * Tests that an invalid view configuration throws an exception.
     *
     * @throws PluginException|InternalErrorException
     */
    public function testInvalidViewConfiguration()
    {
        $this->setExpectedException(InternalErrorException::class, 'View environment missing views path.');

        $options = array(
            ControllerHandler::KEY_BASE_PATH => '/',
            Config::KEY_CONTROLLERS => array(
                'ControllerController' => TestDummyController::class,
            ),
            ControllerHandler::KEY_VIEWS => array()
        );
        $handler = new ControllerHandler($options);
        $handler->isAppropriate('/controller', array(), array(), 'GET');
    }

    /**
     * Tests that the default action of a controller is to render a default
     * view from the view engine.
     *
     * @throws Exception
     */
    public function testRenderDefaultView()
    {
        $routerOptions = array(
            Config::KEY_DI => Di::class,
            Config::KEY_HANDLERS => array(
                'ControllerHandler' => array(
                    Config::KEY_CLASS => ControllerHandler::class,
                    Config::KEY_OPTIONS => array(
                        Config::KEY_CONTROLLERS => array(
                            'TestController' => TestDummyController::class,
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
     *
     * @throws InternalErrorException|PluginException|ResourceNotFoundException
     */
    public function testActionReturnsString()
    {
        $options = array(
            Config::KEY_CONTROLLERS => array(
                'TestController' => TestDummyController::class,
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
     *
     * @throws InternalErrorException|PluginException|ResourceNotFoundException
     */
    public function testActionReturnsArray()
    {
        $options = array(
            Config::KEY_CONTROLLERS => array(
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
     *
     * @throws InternalErrorException|PluginException|ResourceNotFoundException
     */
    public function testActionRendersNonDefaultView()
    {
        $options = array(
            Config::KEY_CONTROLLERS => array(
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
     *
     * @throws InternalErrorException|PluginException|ResourceNotFoundException
     */
    public function testExceptionForNullEncoderRenderView()
    {
        $this->setExpectedException(Exception::class, 'The current encoder does not support the render view method.');

        $options = array(
            Config::KEY_CONTROLLERS => array(
                'TestController' => TestDummyController::class
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
     *
     * @throws InternalErrorException|PluginException|ResourceNotFoundException
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
     *
     * @throws InternalErrorException|PluginException|ResourceNotFoundException
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
