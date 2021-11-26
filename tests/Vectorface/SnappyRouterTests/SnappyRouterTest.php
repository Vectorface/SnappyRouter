<?php

namespace Vectorface\SnappyRouterTests;

use Exception;
use Psr\Log\NullLogger;
use Vectorface\SnappyRouter\SnappyRouter;
use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Handler\ControllerHandler;

use PHPUnit\Framework\TestCase;

/**
 * Tests the main SnappyRouter class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class SnappyRouterTest extends TestCase
{
    /**
     * An overview of how to use the SnappyRouter class.
     *
     * @throws Exception
     */
    public function testSynopsis()
    {
        // an example configuration of the router
        $config = $this->getStandardConfig();
        // instantiate the router
        $router = new SnappyRouter(new Config($config));
        // configure a logger, if insight into router behavior is desired
        $router->setLogger(new NullLogger());

        // an example MVC request
        $_SERVER['REQUEST_URI'] = '/Test/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['param'] = 'value';
        $response = $router->handleRoute('apache2handler');

        $expectedResponse = 'This is a test service.';
        $this->assertEquals($expectedResponse, $response);

        unset($_SERVER['REQUEST_URI']);
        $_GET = [];
        $_POST = [];
    }

    /**
     * Returns a standard router config array.
     * @return array A standard router config.
     */
    private function getStandardConfig()
    {
        return [
            Config::KEY_DI       => 'Vectorface\SnappyRouter\Di\Di',
            Config::KEY_HANDLERS => [
                'BogusCliHandler' => [
                    Config::KEY_CLASS => 'Vectorface\SnappyRouter\Handler\CliTaskHandler'
                ],
                'ControllerHandler' => [
                    Config::KEY_CLASS   => 'Vectorface\SnappyRouter\Handler\ControllerHandler',
                    Config::KEY_OPTIONS => [
                        ControllerHandler::KEY_BASE_PATH => '/',
                        Config::KEY_CONTROLLERS          => [
                            'TestController' => 'Vectorface\SnappyRouterTests\Controller\TestDummyController'
                        ],
                        Config::KEY_PLUGINS => [
                            'TestPlugin' => [
                                Config::KEY_CLASS   => 'Vectorface\SnappyRouterTests\Plugin\TestPlugin',
                                Config::KEY_OPTIONS => []
                            ],
                            'AnotherPlugin' => 'Vectorface\SnappyRouterTests\Plugin\TestPlugin'
                        ]
                    ]
                ],
                'CliHandler' => [
                    Config::KEY_CLASS   => 'Vectorface\SnappyRouter\Handler\CliTaskHandler',
                    Config::KEY_OPTIONS => [
                        'tasks' => [
                            'TestTask' => 'Vectorface\SnappyRouterTests\Task\DummyTestTask'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Tests that the router handles a generic exception.
     *
     * @throws Exception
     */
    public function testGenericException()
    {
        $config = $this->getStandardConfig();
        $router = new SnappyRouter(new Config($config));

        // an example MVC request
        $path = '/Test/genericException';
        $query = ['jsoncall' => 'testMethod'];
        $response = $router->handleHttpRoute($path, $query, [], 'get');

        $expectedResponse = 'A generic exception.';
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Tests that an empty config array results in no handler being found.
     *
     * @throws Exception
     */
    public function testNoHandlerFoundException()
    {
        // turn on debug mode so we get a verbose description of the exception
        $router = new SnappyRouter(new Config([
            'debug' => true
        ]));

        // an example MVC request
        $path = '/Test/test';
        $query = ['jsoncall' => 'testMethod'];
        $response = $router->handleHttpRoute($path, $query, [], 'get');
        $this->assertEquals('No handler responded to the request.', $response);
    }

    /**
     * Tests that an exception is thrown if a handler class does not exist.
     *
     * @throws Exception
     */
    public function testInvalidHandlerClass()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot instantiate instance of Vectorface\SnappyRouter\Handler\NonexistentHandler");

        $config = $this->getStandardConfig();
        $config[Config::KEY_HANDLERS]['InvalidHandler'] = [
            'class' => 'Vectorface\SnappyRouter\Handler\NonexistentHandler'
        ];
        $router = new SnappyRouter(new Config($config));

        // an example MVC request
        $path = '/Test/test';
        $query = ['jsoncall' => 'testMethod'];
        $response = $router->handleHttpRoute($path, $query, [], 'get');

        $expectedResponse = 'No handler responded to request.';
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Tests that the CLI routing functionality works.
     *
     * @throws Exception
     */
    public function testStandardCliRoute()
    {
        $config = $this->getStandardConfig();
        $router = new SnappyRouter(new Config($config));

        $_SERVER['argv'] = [
            'dummyScript.php',
            '--task',
            'TestTask',
            '--action',
            'testMethod'
        ];
        $_SERVER['argc'] = count($_SERVER['argv']);
        $response = $router->handleRoute();

        $expected = 'Hello World'.PHP_EOL;
        $this->assertEquals($expected, $response);
    }

    /**
     * Tests a CLI route that throws an exception.
     *
     * @throws Exception
     */
    public function testCliRouteWithException()
    {
        $config = $this->getStandardConfig();
        $router = new SnappyRouter(new Config($config));

        $_SERVER['argv'] = [
            'dummyScript.php',
            '--task',
            'TestTask',
            '--action',
            'throwsException'
        ];
        $_SERVER['argc'] = count($_SERVER['argv']);
        $response = $router->handleRoute();

        $expected = 'An exception was thrown.'.PHP_EOL;
        $this->assertEquals($expected, $response);
    }

    /**
     * Tests that a CLI route with no appropriate handlers throws an
     * exception.
     *
     * @throws Exception
     */
    public function testCliRouteWithNoHandler()
    {
        $config = $this->getStandardConfig();
        $router = new SnappyRouter(new Config($config));

        $_SERVER['argv'] = [
            'dummyScript.php',
            '--task',
            'NotDefinedTask',
            '--action',
            'anyAction'
        ];
        $_SERVER['argc'] = count($_SERVER['argv']);
        $response = $router->handleRoute();

        $expected = 'No CLI handler registered.'.PHP_EOL;
        $this->assertEquals($expected, $response);
    }
}
