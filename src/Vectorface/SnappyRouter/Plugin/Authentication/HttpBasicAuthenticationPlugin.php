<?php

namespace Vectorface\SnappyRouter\Plugin\Authentication;

use Vectorface\SnappyRouter\Exception\UnauthorizedException;
use Vectorface\SnappyRouter\Handler\AbstractHandler;

/**
 * A plugin to make use of PHP's built-in Auth support to provide HTTP/Basic authentication.
 *
 * Note: This class expects the AuthMechanism DI key set to an AuthenticatorInterface instance.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author J. Anderson <janderson@vectorface.com>
 * @author Dan Bruce   <dbruce@vectorface.com>
 */
class HttpBasicAuthenticationPlugin extends AbstractAuthenticationPlugin
{
    /**
     * The authentication realm, usually presented to the user in a username/password dialog box.
     *
     * @var string
     */
    private $realm = "Authentication Required";

    /**
     * Create a new HTTP/Basic Authentication plugin.
     *
     * @param mixed[] $options An associative array of options. Supports AuthMechanism and realm options.
     */
    public function __construct($options)
    {
        parent::__construct($options);

        if (!empty($options['realm'])) {
            $this->realm = $options['realm'];
        }
    }

    /**
     * Invoked directly after the router decides which handler will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     */
    public function afterHandlerSelected(AbstractHandler $handler)
    {
        try {
            parent::afterHandlerSelected($handler);
        } catch (UnauthorizedException $e) {
            @header(sprintf('WWW-Authenticate: Basic realm="%s"', $this->realm));
            throw $e;
        }
    }

    /**
     * Extract credentials from the request, PHP's PHP_AUTH_(USER|PW) server variables in this case.
     *
     * @return string[] An array of credentials; A username and password pair, or false if credentials aren't available
     */
    public function getCredentials()
    {
        if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
            return array($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        }
        return false;
    }
}
