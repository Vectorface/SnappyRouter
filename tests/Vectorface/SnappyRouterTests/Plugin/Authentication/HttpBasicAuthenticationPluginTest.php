<?php

namespace casino\Tests\engine\ServiceRouter\Plugin\Authentication;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Authentication\CallbackAuthenticator;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Exception\InternalErrorException;
use Vectorface\SnappyRouter\Exception\UnauthorizedException;
use Vectorface\SnappyRouter\Handler\ControllerHandler;
use Vectorface\SnappyRouter\Plugin\Authentication\HttpBasicAuthenticationPlugin;

/**
 * Test for the HttpBasicAuthenticationPlugin class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author J. Anderson <janderson@vectorface.com>
 * @author Dan Bruce   <dbruce@vectorface.com>
 */
class HttpBasicAuthenticationPluginTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the HTTPBasicAuthenticationPlugin; All in one test!
     */
    public function testBasicHTTPAuth()
    {
        $ignored = new ControllerHandler(array());

        /* Configure DI */
        $di = new Di(array('MyCustomAuth' => false));
        Di::setDefault($di);

        /* Direct testing. */
        $plugin = new HttpBasicAuthenticationPlugin(array(
            'AuthMechanism' => 'MyCustomAuth',
            'realm' => 'Authentication Test'
        ));

        try {
            $plugin->afterHandlerSelected($ignored);
            $this->fail("An invalid authenticator should yield an internal error");
        } catch (InternalErrorException $e) {
            $this->assertEquals(500, $e->getAssociatedStatusCode()); /* HTTP 500 ISE */
        }

        /* From here on out, use the "Do whatever I say" authenticator. :) */
        $bool = false;
        $auth = new CallbackAuthenticator(function ($credentials) use (&$bool) {
            return $bool;
        });
        $di->set('MyCustomAuth', $auth);


        $_SERVER['PHP_AUTH_USER'] = $_SERVER['PHP_AUTH_PW'] = null;
        try {
            $plugin->afterHandlerSelected($ignored);
            $this->fail("No username and password are available. UnauthorizedException expected.");
        } catch (UnauthorizedException $e) {
            $this->assertEquals(401, $e->getAssociatedStatusCode()); /* HTTP 401 Unauthorized */
        }

        $_SERVER['PHP_AUTH_USER'] = $_SERVER['PHP_AUTH_PW'] = 'ignored';
        try {
            $plugin->afterHandlerSelected($ignored);
            $this->fail("Callback expected to return false auth result. UnauthorizedException expected.");
        } catch (UnauthorizedException $e) {
            // we expect the exception to be thrown
        }

        /* With a true result, preInvoke should pass through. */
        $bool = true;
        $plugin->afterHandlerSelected($ignored);
    }
}
