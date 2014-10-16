<?php

namespace VectorFace\SnappyRouterTests;

use VectorFace\SnappyRouter\SnappyRouter;
use VectorFace\SnappyRouter\Config\Config;
use VectorFace\SnappyRouter\Plugin\PluginInterface;
use VectorFace\SnappyRouter\Handler\AbstractHandler;

use \PHPUnit_Framework_TestCase;

/**
 * Tests the main SnappyRouter class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class SnappyRouterTest extends PHPUnit_Framework_TestCase
{
    /**
     * An overview of how to use the SnappyRouter class.
     * @test
     */
    public function synopsis()
    {
        // an example configuration of the router
        $config = array(
            SnappyRouter::KEY_DI => 'VectorFace\SnappyRouter\Di\Di',
            SnappyRouter::KEY_HANDLERS => array(
                'ControllerHandler' => array(
                    AbstractHandler::KEY_CLASS => 'VectorFace\SnappyRouter\Handler\ControllerHandler',
                    AbstractHandler::KEY_OPTIONS => array(
                        AbstractHandler::KEY_SERVICES => array(
                            'TestController' => 'VectorFace\SnappyRouterTests\Controller\TestDummyController'
                        ),
                        AbstractHandler::KEY_PLUGINS => array(

                        )
                    )
                )
            )
        );
        // instantiate the router
        $serviceRouter = new SnappyRouter(new Config($config));

        // an example MVC request
        $path = '/Test/test';
        $query = array('jsoncall' => 'testMethod');
        $response = $serviceRouter->handleHttpRoute($path, $query, '', 'get');

        $expectedResponse = array('response' => 'This is a dummy service.');
        $this->assertEquals(json_encode($expectedResponse), $response);
    }

    /**
     * Tests the default settings for an empty config.
     * @depends synopsis
     */
    public function testConfigEmptyArray()
    {
        $serviceRouter = new SnappyRouter();
        $this->assertEquals(array(), $serviceRouter->getHandlers());
        $this->assertEquals(array(), $serviceRouter->getPlugins());
    }

    /**
     * Tests that we get an exception if we request a missing service.
     * @depends synopsis
     * @expectedException casino\engine\ServiceRouter\Exception\ServiceNotFoundForKeyException
     * @expectedExceptionMessage No service was found for key: Missing Service
     */
    public function testMissingService()
    {
        $serviceRouter = new SnappyRouter();
        $serviceRouter->getServiceProvider()->getService('Missing Service');
    }

    /**
     * Tests that we get an exception if we send a garbage plugin.
     * @depends synopsis
     * @expectedException casino\engine\ServiceRouter\Exception\InvalidServiceRouterPluginException
     * @expectedExceptionMessage Invalid configuration for helper.
     */
    public function testBrokenConfig()
    {
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'JSONCallRPCHandler' => 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [
                'ContentHeadersPlugin' => []
            ],
            SnappyRouter::KEY_SERVICES => [
                'DummyTestService' => 'casino\Tests\engine\ServiceRouter\DummyTestService'
            ]
        ];
         $serviceRouter = new SnappyRouter($config);

        $path = '/engine/Tests/DummyTestService.php';
        $response = $serviceRouter->handleRoute($path, array(), '', 'get');
    }

    /**
     * Tests the error case when no handler is responsible for the request.
     * @depends synopsis
     */
    public function testNoAppropriateHandlerRegistered()
    {
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'JSONCallRPCHandler' => 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [
                'ContentHeadersPlugin' => 'casino\engine\ServiceRouter\Plugin\ContentHeadersPlugin'
            ],
            SnappyRouter::KEY_SERVICES => [
                'DummyTestService' => 'casino\Tests\engine\ServiceRouter\DummyTestService'
            ]
        ];
        $serviceRouter = new SnappyRouter($config);

        $path = '/engine/Tests/DummyTestService.php';
        $response = $serviceRouter->handleRoute($path, array(), '', 'get');
        $this->assertEquals('No RPC handler responded to request.', $response);
    }

    /**
     * Tests that configurations can have strange (but allowed) mixed types.
     * @depends synopsis
     */
    public function testBizzareConfiguration()
    {
        $handlers = new \stdClass;
        $handlers->JSONCallRPCHandler = 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler';
        $config = array(
            SnappyRouter::KEY_HANDLERS => $handlers, // an object
            SnappyRouter::KEY_PLUGINS => 'BrokenConfigElement', // single element
            SnappyRouter::KEY_SERVICES => array(
                'DummyTestService'  => 'casino\Tests\engine\ServiceRouter\DummyTestService'
            )
        );
        $serviceRouter = new SnappyRouter($config);

        $this->assertTrue(is_array($serviceRouter->getHandlers()));
        $this->assertEquals(1, count($serviceRouter->getHandlers()));

        $this->assertTrue(is_array($serviceRouter->getPlugins()));
        $this->assertEquals(0, count($serviceRouter->getPlugins()));
    }

    /**
     * Tests that an exception is thrown when a handler is pointed at a file that doesn't exist.
     * @depends synopsis
     * @expectedException casino\engine\ServiceRouter\Exception\InvalidServiceRouterHandlerException
     * @expectedExceptionMessage Class missing\Handler\Class does not exist.
     */
    public function testMissingHandler()
    {
        $config = array(
            SnappyRouter::KEY_HANDLERS => array('InvalidHandler' => 'missing\Handler\Class'),
            SnappyRouter::KEY_PLUGINS => array(),
            SnappyRouter::KEY_SERVICES => array()
        );
        $serviceRouter = new SnappyRouter($config);
    }

    /**
     * Tests that an exception is thrown when a plugin is pointed at a file that doesn't exist.
     * @depends synopsis
     * @expectedException casino\engine\ServiceRouter\Exception\InvalidServiceRouterPluginException
     * @expectedExceptionMessage Class missing\Plugin\Class does not exist.
     */
    public function testMissingPlugin()
    {
        $config = array(
            SnappyRouter::KEY_HANDLERS => array(),
            SnappyRouter::KEY_PLUGINS => array('InvalidPlugin' => 'missing\Plugin\Class'),
            SnappyRouter::KEY_SERVICES => array()
        );
        $serviceRouter = new SnappyRouter($config);
    }

    /**
     * Tests that an exception is thrown when a handler class fails to implement the interface.
     * @depends synopsis
     */
    public function testHandlerDoesNotImplementInterface()
    {
        $this->setExpectedException(
            'casino\engine\ServiceRouter\Exception\InvalidServiceRouterHandlerException',
            'Class casino\engine\ServiceRouter\Handler\JSONCallRPCHandler does not implement '.
            'casino\engine\ServiceRouter\Handler\HandlerInterface'
        );
        $mockClass = $this->getMockBuilder(
            'ReflectionClass',
            array('implementsInterface')
        )->setConstructorArgs(
            array('stdClass')
        )->getMock();

        $mockClass->expects($this->any())->method('implementsInterface')->will(
            $this->returnValue(false)
        );

        $config = array(
            SnappyRouter::KEY_HANDLERS => array(
                'JSONCallRPCHandler' => 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler'
            ),
            SnappyRouter::KEY_PLUGINS => array(),
            SnappyRouter::KEY_SERVICES => array()
        );
        $serviceRouter = new SnappyRouter($config, get_class($mockClass));
    }

    /**
     * Tests that an exception is thrown when a plugin class fails to implement the interface.
     * @depends synopsis
     */
    public function testPluginDoesNotImplementInterface()
    {
        $this->setExpectedException(
            'casino\engine\ServiceRouter\Exception\InvalidServiceRouterPluginException',
            'Class casino\engine\ServiceRouter\Plugin\ContentHeadersPlugin does not implement '.
            'casino\engine\ServiceRouter\Plugin\RPCPluginInterface'
        );
        $mockClass = $this->getMockBuilder(
            'ReflectionClass',
            array('implementsInterface')
        )->setConstructorArgs(
            array('stdClass')
        )->getMock();

        $mockClass->expects($this->any())->method('implementsInterface')->will(
            $this->returnValue(false)
        );

        $config = [
            SnappyRouter::KEY_HANDLERS => [],
            SnappyRouter::KEY_PLUGINS => [
                'ContentHeadersPlugin' => 'casino\engine\ServiceRouter\Plugin\ContentHeadersPlugin'
            ],
            SnappyRouter::KEY_SERVICES => []
        ];
        $serviceRouter = new SnappyRouter($config, $mockClass);
    }

    /**
     * Tests the error when a non existent method is invoked.
     * @depends synopsis
     */
    public function testNonExistentMethodInvoked()
    {
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'JSONCallRPCHandler' => 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [
                'ContentHeadersPlugin' => 'casino\engine\ServiceRouter\Plugin\ContentHeadersPlugin'
            ],
            SnappyRouter::KEY_SERVICES => [
                'DummyTestService' => 'casino\Tests\engine\ServiceRouter\DummyTestService'
            ]
        ];
        $serviceRouter = new SnappyRouter($config);

        $path = '/engine/Tests/DummyTestService.php';
        $query = array('jsoncall' => 'nonExistentMethod');
        $response = $serviceRouter->handleRoute($path, $query, '', 'get');

        $expectedResponse = [
            'error' => 'Service DummyTestService does not exist or does not have method nonExistentMethod'
        ];
        $this->assertEquals(json_encode($expectedResponse), $response);
    }

    /**
     * Tests the error when a non public method is invoked.
     * @depends synopsis
     */
    public function testCallToNonPublicMethod()
    {
        // instantiate the class
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'JSONCallRPCHandler' => 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [
                'JSONCallMethodSecurityPlugin' => 'casino\engine\ServiceRouter\Plugin\JSONCallMethodSecurityPlugin',
                'ContentHeadersPlugin' => 'casino\engine\ServiceRouter\Plugin\ContentHeadersPlugin'
            ],
            SnappyRouter::KEY_SERVICES => [
                'DummyTestService' => 'casino\Tests\engine\ServiceRouter\DummyTestService'
            ]
        ];
        $serviceRouter = new SnappyRouter($config);

        $path = '/engine/Tests/DummyTestService.php';
        $query = array('jsoncall' => 'nonExposedMethod');
        $response = $serviceRouter->handleRoute($path, $query, '', 'get');

        $expected = [
            'error' => 'Method nonExposedMethod does not exist or is not published.'
        ];
        $this->assertEquals(json_encode($expected), $response);
    }

    /**
     * Tests the JSONPResponse plugin.
     * @depends synopsis
     */
    public function testJSONPResponse()
    {
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'JSONCallRPCHandler' => 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [
                'JSONCallMethodSecurityPlugin' => 'casino\engine\ServiceRouter\Plugin\JSONCallMethodSecurityPlugin',
                'ContentHeadersPlugin' => 'casino\engine\ServiceRouter\Plugin\ContentHeadersPlugin',
                'JSONPResponsePlugin' => 'casino\engine\ServiceRouter\Plugin\JSONPResponsePlugin'
            ],
            SnappyRouter::KEY_SERVICES => [
                'DummyTestJSONPResponderService' => 'casino\Tests\engine\ServiceRouter\DummyTestJSONPResponderService'
            ]
        ];
        $serviceRouter = new SnappyRouter($config);

        $path = '/engine/Tests/DummyTestJSONPResponderService.php';
        $query = array('jsoncall' => 'testMethod', 'jsonp' => 'clientSideMethod');
        $response = $serviceRouter->handleRoute($path, $query, '', 'get');

        $expectedResponse = 'clientSideMethod('.json_encode(array('response' => 'This is a dummy service.')).');';
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Tests the JSONPResponse plugin.
     * @depends synopsis
     */
    public function testJSONPResponseForNonResponder()
    {
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'JSONCallRPCHandler' => 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [
                'JSONCallMethodSecurityPlugin' => 'casino\engine\ServiceRouter\Plugin\JSONCallMethodSecurityPlugin',
                'ContentHeadersPlugin' => 'casino\engine\ServiceRouter\Plugin\ContentHeadersPlugin',
                'JSONPResponsePlugin' => 'casino\engine\ServiceRouter\Plugin\JSONPResponsePlugin'
            ],
            SnappyRouter::KEY_SERVICES => array(
                'DummyTestService' => 'casino\Tests\engine\ServiceRouter\DummyTestService'
            )
        ];
        $serviceRouter = new SnappyRouter($config);

        $path = '/engine/Tests/DummyTestService.php';
        $query = array('jsoncall' => 'testMethod', 'jsonp' => 'clientSideMethod');
        $response = $serviceRouter->handleRoute($path, $query, '', 'get');

        $expectedResponse = json_encode(array('response' => 'This is a dummy service.'));
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Test that asserts we can load a service that is not in any namespace.
     * @depends synopsis
     */
    public function testLoadingNonNamespacedService()
    {
        $servicePath = realpath(__DIR__.'/LegacyDummyTestService.php');
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'JSONCallRPCHandler' => 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [
                'JSONCallMethodSecurityPlugin' => 'casino\engine\ServiceRouter\Plugin\JSONCallMethodSecurityPlugin',
                'ContentHeadersPlugin' => 'casino\engine\ServiceRouter\Plugin\ContentHeadersPlugin',
                'JSONPResponsePlugin' => 'casino\engine\ServiceRouter\Plugin\JSONPResponsePlugin'
            ],
            SnappyRouter::KEY_SERVICES => array(
                'LegacyDummyTestService' => $servicePath
            )
        ];
        $serviceRouter = new SnappyRouter($config);

        $path = '/engine/Tests/LegacyDummyTestService.php';
        $query = array('jsoncall' => 'testMethod', 'jsonp' => 'clientSideMethod');
        $response = $serviceRouter->handleRoute($path, $query, '', 'get');

        $expectedResponse = json_encode(array('response' => 'This is a dummy service.'));
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Test that an MVC controller can handle a full route.
     * @depends synopsis
     */
    public function testMvcRoute()
    {
        $servicePath = realpath(__DIR__.'/LegacyDummyTestService.php');
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'MvcApplicationHandler' => 'casino\engine\ServiceRouter\Handler\MvcApplicationHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [
                'ContentHeadersPlugin' => 'casino\engine\ServiceRouter\Plugin\ContentHeadersPlugin',
                'ServiceRouterHeaderPlugin' => 'casino\engine\ServiceRouter\Plugin\ServiceRouterHeaderPlugin',
                'MvcApplicationPlugin' => 'casino\engine\ServiceRouter\Plugin\MvcApplicationPlugin'
            ],
            SnappyRouter::KEY_SERVICES => array(
                'IndexController' => 'casino\Tests\engine\ServiceRouter\Mvc\fixtures\Controllers\IndexController'
            ),
            'assets' => [],
            'basePath' => '/',
            'views' => [
                'path' => realpath(__DIR__.'/Mvc/fixtures/views')
            ]
        ];
        $serviceRouter = new SnappyRouter($config);
        $response = $serviceRouter->handleRoute('/', [], '', 'GET');
        $expectedResponse = 'Hello world 1234';
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Tests that we have a working CLI route.
     * @depends synopsis
     */
    public function testCLIRoute()
    {
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'JSONCallRPCHandler' => 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler',
                'CLIRequestHandler'  => 'casino\engine\ServiceRouter\Handler\CLIRequestHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [],
            SnappyRouter::KEY_SERVICES => [],
            SnappyRouter::KEY_TASKS => [
                'TestdummyTask' => 'casino\Tests\engine\ServiceRouter\TestDummyTask'
            ]
        ];
        ob_start();
        $serviceRouter = new SnappyRouter($config);
        $serviceRouter->handleCLIRoute([
            'task' => 'TestDummy',
            'action' => 'dummyMethod',
            'params' => [1, 2, 3]
        ]);
        $response = ob_get_clean();
        $this->assertEquals(json_encode([1, 2, 3]), $response);
    }

    /**
     * Tests that we have a working CLI route.
     * @depends synopsis
     * @expectedException \Exception
     * @expectedExceptionMessage No task registered for TestdummyTask
     */
    public function testCLIRouteWithMissingTask()
    {
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'JSONCallRPCHandler' => 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler',
                'CLIRequestHandler'  => 'casino\engine\ServiceRouter\Handler\CLIRequestHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [],
            SnappyRouter::KEY_SERVICES => [],
            SnappyRouter::KEY_TASKS => []
        ];
        $serviceRouter = new SnappyRouter($config);
        $serviceRouter->handleCLIRoute([
            'task' => 'TestDummy',
            'action' => 'dummyMethod',
            'params' => [1, 2, 3]
        ]);
    }

    /**
     * Tests that we get an exception when there are missing CLI arguments.
     * @depends synopsis
     * @expectedException casino\engine\ServiceRouter\Exception\InvalidServiceRouterHandlerException
     * @expectedExceptionMessage Unable to find suitable handler for CLI route.
     */
    public function testCLIRouteWithMissingArguments()
    {
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'CLIRequestHandler'  => 'casino\engine\ServiceRouter\Handler\CLIRequestHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [],
            SnappyRouter::KEY_SERVICES => [],
            SnappyRouter::KEY_TASKS => []
        ];
        $serviceRouter = new SnappyRouter($config);
        $serviceRouter->handleCLIRoute([]);
    }

    /**
     * Tests all the various blacklist and whitelist possible configurations.
     * @depends synopsis
     */
    public function testServicePluginCompatibilityList()
    {
        $config = [
            SnappyRouter::KEY_HANDLERS => [
                'JSONCallRequestHandler'  => 'casino\engine\ServiceRouter\Handler\JSONCallRPCHandler'
            ],
            SnappyRouter::KEY_PLUGINS => [
                'NoWhitelistOrBlacklist' => [
                    'class' => 'casino\engine\ServiceRouter\Plugin\ServiceRouterHeaderPlugin'
                ],
                'NotWhitelisted' => [
                    'class' => 'casino\engine\ServiceRouter\Plugin\ServiceRouterHeaderPlugin',
                    'enable' => []
                ],
                'WhitelistedWithAll' => [
                    'class' => 'casino\engine\ServiceRouter\Plugin\ServiceRouterHeaderPlugin',
                    'enable' => [
                        'DummyTestService' => PluginInterface::METHOD_KEY_ALL
                    ]
                ],
                'MethodSpecificallyWhitelisted' => [
                    'class' => 'casino\engine\ServiceRouter\Plugin\ServiceRouterHeaderPlugin',
                    'enable' => [
                        'DummyTestService' => ['testMethod']
                    ]
                ],
                'BlacklistedWithAll' => [
                    'class' => 'casino\engine\ServiceRouter\Plugin\ServiceRouterHeaderPlugin',
                    'disable' => [
                        'DummyTestService' => PluginInterface::METHOD_KEY_ALL
                    ]
                ],
                'NotBlacklisted' => [
                    'class' => 'casino\engine\ServiceRouter\Plugin\ServiceRouterHeaderPlugin',
                    'disable' => []
                ],
                'MethodSpecificallyBlacklisted' => [
                    'class' => 'casino\engine\ServiceRouter\Plugin\ServiceRouterHeaderPlugin',
                    'disable' => [
                        'DummyTestService' => ['testMethod']
                    ]
                ],
            ],
            SnappyRouter::KEY_SERVICES => [
                'DummyTestService' => 'casino\Tests\engine\ServiceRouter\DummyTestService'
            ],
            SnappyRouter::KEY_TASKS => []
        ];
        $serviceRouter = new SnappyRouter($config);
        $path = '/engine/Tests/DummyTestService.php';
        $query = array('jsoncall' => 'testMethod');
        $serviceRouter->handleRoute($path, $query, '', 'GET');
    }
}
