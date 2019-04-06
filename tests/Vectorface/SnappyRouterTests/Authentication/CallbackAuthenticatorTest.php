<?php

namespace Vectorface\SnappyRouterTests\Authentication;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Authentication\CallbackAuthenticator;

class CallbackAuthenticatorTest extends TestCase
{
    /**
     * A basic test showing standard functionality.
     * The fact that no exception is thrown indicates we do not exceed any of
     * the listed rules.
     */
    public function testAuthenticator()
    {
        $bool = true;
        $auth = new CallbackAuthenticator(function () use ($bool) {
            return $bool;
        });
        $this->assertTrue($auth->authenticate(array('a', 'b')));

        $bool = false;
        $auth = new CallbackAuthenticator(function () use ($bool) {
            return $bool;
        });
        $this->assertFalse($auth->authenticate(array('a', 'b')));
    }
}
