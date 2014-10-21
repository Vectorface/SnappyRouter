<?php

namespace Vectorface\SnappyRouterTests\Plugin\AccessControl;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Handler\ControllerHandler;
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
        $controller = new TestDummyController();
        $action = 'testAction';
        // fake an OPTIONS request as part of the cross origin request spec
        $request = new HttpRequest('TestDummyController', $action, 'OPTIONS');

        // configure the plugin to allow cross origin access to all methods
        // within the TestDummyController controller
        $plugin = new CrossOriginRequestPlugin(array(
            'whitelist' => array(
                'TestDummyController' => 'all'
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
        $this->assertNull(
            $plugin->afterControllerSelected(
                $handler,
                $request,
                $controller,
                $action
            )
        );
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
        $controller = new TestDummyController();
        $action = 'testAction';
        $request = new HttpRequest('TestDummyController', $action, 'GET');

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
        $this->assertNull(
            $plugin->afterControllerSelected(
                $handler,
                $request,
                $controller,
                $action
            )
        );

        // set the origin to a domain in the ignored whitelist
        $_SERVER['HTTP_ORIGIN'] = 'www.example.com';
        $this->assertNull(
            $plugin->afterControllerSelected(
                $handler,
                $request,
                $controller,
                $action
            )
        );
        // cleanup
        unset($_SERVER['HTTP_ORIGIN']);
    }

    /**
     * Tests that we get an access denied exception if a cross origin request
     * is generated but we are missing a whitelist.
     * @expectedException Vectorface\SnappyRouter\Exception\AccessDeniedException
     * @expectedExceptionMessage Cross origin access denied to TestDummyController and action testAction
     */
    public function testAccessDeniedWithMissingWhitelist()
    {
        // make it appear that we are generating a cross origin request
        $_SERVER['HTTP_ORIGIN'] = 'www.example.com';
        // some dummy variables that are needed by the plugin
        $handler = new ControllerHandler(array());
        $controller = new TestDummyController();
        $action = 'testAction';
        $request = new HttpRequest('TestDummyController', $action, 'GET');

        $plugin = new CrossOriginRequestPlugin(array());
        $plugin->afterControllerSelected($handler, $request, $controller, $action);
    }

    /**
     * Tests that access is denied to an action not listed in the whitelist of
     * the controller.
     * @expectedException Vectorface\SnappyRouter\Exception\AccessDeniedException
     * @expectedExceptionMessage Cross origin access denied to TestDummyController and action testAction
     */
    public function testAccessDeniedToActionMissingFromWhitelist()
    {
        // make it appear that we are generating a cross origin request
        $_SERVER['HTTP_ORIGIN'] = 'www.example.com';
        // some dummy variables that are needed by the plugin
        $handler = new ControllerHandler(array());
        $controller = new TestDummyController();
        $action = 'testAction';
        $request = new HttpRequest('TestDummyController', $action, 'GET');

        $plugin = new CrossOriginRequestPlugin(array(
            'whitelist' => array(
                'TestDummyController' => array()
            )
        ));
        $plugin->afterControllerSelected($handler, $request, $controller, $action);
    }
}
