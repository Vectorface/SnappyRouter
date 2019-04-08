<?php

namespace Vectorface\SnappyRouterTests\Plugin\Authentication;

use PHPUnit\Framework\TestCase;

use Vectorface\SnappyRouter\Authentication\CallbackAuthenticator;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Exception\InternalErrorException;
use Vectorface\SnappyRouter\Exception\UnauthorizedException;
use Vectorface\SnappyRouter\Handler\ControllerHandler;

/**
 * Tests the AbstractAuthenticationPlugin class.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author J. Anderson <janderson@vectorface.com>
 * @author Dan Bruce   <dbruce@vectorface.com>
 */
class AbstractAuthenticationPluginTest extends TestCase
{
    /**
     * Authentication of service requests happens by intercepting preInvoke; Validate that.
     */
    public function testAfterHandlerInvoked()
    {
        $ignored = new ControllerHandler(array());

        /* Configure DI */
        $bool = false;
        $auth = new CallbackAuthenticator(function () use (&$bool) {
            return $bool;
        });
        $di = new Di(array('AuthMechanism' => false));
        Di::setDefault($di);

        /* Direct testing. */
        $plugin = new TestAuthenticationPlugin(array());

        try {
            $plugin->afterHandlerSelected($ignored);
            $this->fail("An invalid authenticator should yield an internal error");
        } catch (InternalErrorException $e) {
            $this->assertEquals(500, $e->getAssociatedStatusCode()); /* HTTP 500 ISE */
        }

        /* From here on out, use the "Do whatever I say" authenticator. :) */
        $di->set('AuthMechanism', $auth);

        $plugin->credentials = false;
        try {
            $plugin->afterHandlerSelected($ignored);
            $this->fail("No username and password are available. UnauthorizedException expected.");
        } catch (UnauthorizedException $e) {
            $this->assertEquals(401, $e->getAssociatedStatusCode()); /* HTTP 401 Unauthorized */
        }

        $plugin->credentials = array('ignored' , 'ignored');
        try {
            $plugin->afterHandlerSelected($ignored);
            $this->fail("Callback expected to return false auth result. UnauthorizedException expected.");
        } catch (UnauthorizedException $e) {
            // we expect the exception to be thrown
        }

        /* With a true result, preInvoke should pass through. */
        $bool = true;
        $this->assertTrue($bool);
        $plugin->afterHandlerSelected($ignored);
    }
}
