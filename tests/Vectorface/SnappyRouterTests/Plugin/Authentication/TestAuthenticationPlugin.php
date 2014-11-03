<?php

namespace Vectorface\SnappyRouterTests\Plugin\Authentication;

use Vectorface\SnappyRouter\Plugin\Authentication\AbstractAuthenticationPlugin;

/**
 * A plugin to allow testing the abstract AbstractAuthencationPlugin class.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author J. Anderson <janderson@vectorface.com>
 */
class TestAuthenticationPlugin extends AbstractAuthenticationPlugin
{
    /**
     * The "credentials" returned by getCredentials for testing.
     *
     * @var mixed
     */
    public $credentials = array('ignored', 'ignored');

    /**
     * Extract credentials from the "request"... Or the hard-coded test values above.
     *
     * @return string[] An array of credentials; A username and password pair, or false if credentials aren't available
     */
    public function getCredentials()
    {
        return $this->set('credentials', $this->credentials)->get('credentials');
    }
}
