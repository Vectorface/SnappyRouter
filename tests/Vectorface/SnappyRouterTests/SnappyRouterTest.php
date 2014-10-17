<?php

namespace Vectorface\SnappyRouterTests;

use Vectorface\SnappyRouter\SnappyRouter;
use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Plugin\PluginInterface;
use Vectorface\SnappyRouter\Handler\AbstractHandler;

use \PHPUnit_Framework_TestCase;

/**
 * Tests the main SnappyRouter class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class SnappyRouterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Returns a standard router config array.
     * @return array A standard router config.
     */
    private function getStandardConfig()
    {
        return array(
            SnappyRouter::KEY_DI => 'Vectorface\SnappyRouter\Di\Di',
            SnappyRouter::KEY_HANDLERS => array(
                'ControllerHandler' => array(
                    AbstractHandler::KEY_CLASS => 'Vectorface\SnappyRouter\Handler\ControllerHandler',
                    AbstractHandler::KEY_OPTIONS => array(
                        AbstractHandler::KEY_SERVICES => array(
                            'TestController' => 'Vectorface\SnappyRouterTests\Controller\TestDummyController'
                        ),
                        AbstractHandler::KEY_PLUGINS => array(
                            'TestPlugin'     => array(
                                AbstractHandler::KEY_CLASS => 'Vectorface\SnappyRouterTests\Plugin\TestPlugin',
                                AbstractHandler::KEY_OPTIONS => array()
                            ),
                            'AnotherPlugin'  => 'Vectorface\SnappyRouterTests\Plugin\TestPlugin'
                        )
                    )
                )
            )
        );
    }

    /**
     * An overview of how to use the SnappyRouter class.
     * @test
     */
    public function synopsis()
    {
        // an example configuration of the router
        $config = $this->getStandardConfig();
        // instantiate the router
        $router = new SnappyRouter(new Config($config));

        // an example MVC request
        $path = '/Test/test';
        $query = array('jsoncall' => 'testMethod');
        $response = $router->handleHttpRoute($path, $query, '', 'get');

        $expectedResponse = 'This is a test service.';
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Tests that the router handles a generic exception.
     */
    public function testGenericException()
    {
        $config = $this->getStandardConfig();
        $router = new SnappyRouter(new Config($config));

        // an example MVC request
        $path = '/Test/genericException';
        $query = array('jsoncall' => 'testMethod');
        $response = $router->handleHttpRoute($path, $query, '', 'get');

        $expectedResponse = 'A generic exception.';
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Tests that an empty config array results in no handler being found.
     */
    public function testNoHandlerFoundException()
    {
        $router = new SnappyRouter(new Config(array()));

        // an example MVC request
        $path = '/Test/test';
        $query = array('jsoncall' => 'testMethod');
        $response = $router->handleHttpRoute($path, $query, '', 'get');

        $expectedResponse = 'No handler responded to request.';
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Tests that an exception is thrown if a handler class does not exist.
     * @expectedException Exception
     * @expectedExceptionMessage Cannot instantiate instance of Vectorface\SnappyRouter\Handler\NonexistantHandler
     */
    public function testInvalidHandlerClass()
    {
        $config = $this->getStandardConfig();
        $config[SnappyRouter::KEY_HANDLERS]['InvalidHandler'] = array(
            'class' => 'Vectorface\SnappyRouter\Handler\NonexistantHandler'
        );
        $router = new SnappyRouter(new Config($config));

        // an example MVC request
        $path = '/Test/test';
        $query = array('jsoncall' => 'testMethod');
        $response = $router->handleHttpRoute($path, $query, '', 'get');

        $expectedResponse = 'No handler responded to request.';
        $this->assertEquals($expectedResponse, $response);
    }
}
