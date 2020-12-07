<?php

namespace Vectorface\SnappyRouterTests\Plugin\AccessControl;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Exception\AccessDeniedException;
use Vectorface\SnappyRouter\Exception\InternalErrorException;
use Vectorface\SnappyRouter\Exception\PluginException;
use Vectorface\SnappyRouter\Handler\ControllerHandler;
use Vectorface\SnappyRouter\Handler\PatternMatchHandler;
use Vectorface\SnappyRouter\Handler\JsonRpcHandler;
use Vectorface\SnappyRouter\Plugin\AccessControl\CrossOriginRequestPlugin;
use Vectorface\SnappyRouterTests\Controller\TestDummyController;
use Vectorface\SnappyRouterTests\Handler\JsonRpcHandlerTest;

/**
 * Tests the router header plugin.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class CrossOriginRequestPluginTest extends TestCase
{
    /**
     * An overview of how to use the plugin.
     *
     * @throws AccessDeniedException|InternalErrorException|PluginException
     */
    public function testSynopsis()
    {
        // make it appear that we are generating a cross origin request
        $_SERVER['HTTP_ORIGIN'] = 'cross.example.com';
        // some dummy variables that are needed by the plugin
        $handler = new ControllerHandler(array());
        $this->assertTrue($handler->isAppropriate('/testDummy', array(), array(), 'OPTIONS'));

        // configure the plugin to allow cross origin access to all methods
        // within the TestDummyController controller
        $plugin = new CrossOriginRequestPlugin(array(
            'whitelist' => array(
                'TestdummyController' => 'all'
            ),
            'ignoreOrigins' => array(
                'www.example.com'
            ),
            'Access-Control-Max-Age' => 3600,
            'Access-Control-Allow-Headers' => array('accept', 'content-type', 'content-length'),
            'Access-Control-Allow-Methods' => array('GET', 'POST', 'OPTIONS', 'PUT', 'DELETE')
        ));

        // if the plugin allows the cross origin request then no exception
        // should be thrown
        $plugin->afterHandlerSelected($handler);
        // a bit of cleanup
        unset($_SERVER['HTTP_ORIGIN']);
    }

    /**
     * Tests that a non-cross origin request simply bypasses the plugin.
     *
     * @throws AccessDeniedException|InternalErrorException|PluginException
     */
    public function testNonCrossOriginRequests()
    {
        // make it appear that we are not generating a cross origin request
        unset($_SERVER['HTTP_ORIGIN']);
        // some dummy variables that are needed by the plugin
        $handler = new ControllerHandler(array());
        $this->assertTrue($handler->isAppropriate('/testDummy', array(), array(), 'GET'));
        // an empty whitelist indicates that every cross origin request should
        // be blocked
        $plugin = new CrossOriginRequestPlugin(array(
            'whitelist' => array(),
            'ignoreOrigins' => array(
                'www.example.com'
            )
        ));

        // this request should not be a cross origin one so no exception should
        // be thrown
        $plugin->afterHandlerSelected($handler);

        // set the origin to a domain in the ignored whitelist
        $_SERVER['HTTP_ORIGIN'] = 'www.example.com';
        $plugin->afterHandlerSelected($handler);
        // cleanup
        unset($_SERVER['HTTP_ORIGIN']);
    }

    /**
     * Tests that we get an exception if the whitelist is missing from the
     * plugin configuration.
     *
     * @throws AccessDeniedException|InternalErrorException|PluginException
     */
    public function testMissingWhitelistGeneratesException()
    {
        // make it appear that we are generating a cross origin request
        $_SERVER['HTTP_ORIGIN'] = 'www.example.com';
        // some dummy variables that are needed by the plugin
        $handler = new ControllerHandler(array());
        $this->assertTrue($handler->isAppropriate('/testDummy', array(), array(), 'GET'));
        $plugin = new CrossOriginRequestPlugin(array());
        try {
            $plugin->afterHandlerSelected($handler);
            $this->fail();
        } catch (InternalErrorException $e) {
            $this->assertEquals(500, $e->getAssociatedStatusCode());
        }
    }

    /**
     * Tests that access is denied to a service not listed in the whitelist.
     *
     * @throws InternalErrorException
     */
    public function testAccessDeniedToServiceMissingFromWhitelist()
    {
        // make it appear that we are generating a cross origin request
        $_SERVER['HTTP_ORIGIN'] = 'www.example.com';
        // some dummy variables that are needed by the plugin
        $handler = new ControllerHandler(array());
        $this->assertTrue($handler->isAppropriate('/testDummy', array(), array(), 'GET'));
        $plugin = new CrossOriginRequestPlugin(array(
            'whitelist' => array()
        ));
        try {
            $plugin->afterHandlerSelected($handler);
        } catch (AccessDeniedException $e) {
            $this->assertEquals(403, $e->getAssociatedStatusCode());
        }
    }

    /**
     * Tests that access is denied to an action not listed in the whitelist of
     * the controller.
     *
     * @throws PluginException|InternalErrorException
     */
    public function testAccessDeniedToActionMissingFromWhitelist()
    {
        // make it appear that we are generating a cross origin request
        $_SERVER['HTTP_ORIGIN'] = 'www.example.com';
        // some dummy variables that are needed by the plugin
        $handler = new ControllerHandler(array());
        $this->assertTrue($handler->isAppropriate('/testDummy', array(), array(), 'GET'));
        $plugin = new CrossOriginRequestPlugin(array(
            'whitelist' => array(
                'TestdummyController' => array()
            )
        ));
        try {
            $plugin->afterHandlerSelected($handler);
        } catch (AccessDeniedException $e) {
            $this->assertEquals(403, $e->getAssociatedStatusCode());
        }
    }

    /**
     * Tests that the whitelist can be the string 'all' instead of an array
     * allowing access to any service.
     *
     * @throws InternalErrorException|PluginException
     */
    public function testWhitelistingAllActions()
    {
        // make it appear that we are generating a cross origin request
        $_SERVER['HTTP_ORIGIN'] = 'www.example.com';
        // some dummy variables that are needed by the plugin
        $handler = new ControllerHandler(array());
        $this->assertTrue($handler->isAppropriate('/testDummy', array(), array(), 'GET'));
        $plugin = new CrossOriginRequestPlugin(array(
            'whitelist' => 'all'
        ));
        try {
            $plugin->afterHandlerSelected($handler);
            $this->assertTrue(true);
        } catch (AccessDeniedException $e) {
            $this->fail('Cross origin plugin should not have denied access.');
        }
    }

    /**
     * Test that the plugin doesn't break with the PatternMatchHandler.
     *
     * @throws InternalErrorException|PluginException
     */
    public function testCompatibilityWithPatternMatchHandler()
    {
        // make it appear that we are generating a cross origin request
        $_SERVER['HTTP_ORIGIN'] = 'www.example.com';
        // some dummy variables that are needed by the plugin
        $config = array(
            'routes' => array(
                '/testDummy' => function () {
                    return true;
                }
            )
        );
        $handler = new PatternMatchHandler($config);
        $this->assertTrue($handler->isAppropriate('/testDummy', array(), array(), 'GET'));
        $plugin = new CrossOriginRequestPlugin(array(
            'whitelist' => 'all'
        ));
        try {
            $plugin->afterHandlerSelected($handler);
            $this->assertTrue(true);
        } catch (AccessDeniedException $e) {
            $this->fail('Cross origin plugin should not have denied access.');
        }
    }

    /**
     * Test that the plugin doesn't break with the PatternMatchHandler.
     *
     * @throws InternalErrorException|PluginException
     */
    public function testCompatibilityWithJsonRpcHandler()
    {
        // make it appear that we are generating a cross origin request
        $_SERVER['HTTP_ORIGIN'] = 'www.example.com';
        // some dummy variables that are needed by the plugin
        $config = array(
            'controllers' => array(
                'TestController' => TestDummyController::class,
            )
        );
        $handler = new JsonRpcHandler($config);
        $payload = array('jsonrpc' => '2.0', 'method' => 'testAction', 'id' => '1');
        JsonRpcHandlerTest::setRequestPayload($handler, $payload);
        $this->assertTrue($handler->isAppropriate('/testDummy', array(), array(), 'POST'));
        $plugin = new CrossOriginRequestPlugin(array(
            'whitelist' => array(
                'testDummy' => array('testAction')
            )
        ));
        try {
            $plugin->afterHandlerSelected($handler);
            $this->assertTrue(true);
        } catch (AccessDeniedException $e) {
            $this->fail('Cross origin plugin should not have denied access.');
        }
    }
}
