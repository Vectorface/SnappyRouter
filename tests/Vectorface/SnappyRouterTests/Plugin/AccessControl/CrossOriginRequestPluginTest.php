<?php

namespace Vectorface\SnappyRouterTests\Plugin\AccessControl;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Exception\AccessDeniedException;
use Vectorface\SnappyRouter\Exception\InternalErrorException;
use Vectorface\SnappyRouter\Handler\ControllerHandler;
use Vectorface\SnappyRouter\Handler\PatternMatchHandler;
use Vectorface\SnappyRouter\Plugin\AccessControl\CrossOriginRequestPlugin;
use Vectorface\SnappyRouter\Request\HttpRequest;
use Vectorface\SnappyRouterTests\Controller\TestDummyController;

/**
 * Tests the router header plugin.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class CrossOriginRequestPluginTest extends PHPUnit_Framework_TestCase
{
    /**
     * An overview of how to use the plugin.
     * @test
     */
    public function synopsis()
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
        $this->assertNull($plugin->afterHandlerSelected($handler));
        // a bit of cleanup
        unset($_SERVER['HTTP_ORIGIN']);
    }

    /**
     * Tests that a non-cross origin request simply bypasses the plugin.
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
        $this->assertNull($plugin->afterHandlerSelected($handler));

        // set the origin to a domain in the ignored whitelist
        $_SERVER['HTTP_ORIGIN'] = 'www.example.com';
        $this->assertNull($plugin->afterHandlerSelected($handler));
        // cleanup
        unset($_SERVER['HTTP_ORIGIN']);
    }

    /**
     * Tests that we get an exception if the whitelist is missing from the
     * plugin configuration.
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
}
