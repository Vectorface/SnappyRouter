<?php

namespace Vectorface\SnappyRouter\Authentication;

/**
 * An interface to be implemented by code intending to authenticate via the service router.
 *
 * This will often be a wrapper around other authentication/authorization classes, for example:
 * \code{.php}
 * class MyAuth implements AuthenticationInterface {
 *     public function __construct($wrappedAuthMechanism) {
 *         $this->realAuthMechanism = $wrappedAuthMechanism;
 *     }
 *     public function authenticate($cred) {
 *         return $this->realAuthMechanism->login($cred->user, $cred->pass);
 *     }
 * }
 * \endcode
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author J. Anderson <janderson@vectorface.com>
 */
interface AuthenticatorInterface
{
    /**
     * Authenticate a set of credentials, typically a username and password.
     *
     * @param mixed $credentials Credentials in some form; A string username and password, an auth token, etc.
     * @return bool Returns true if the identity was authenticated, or false otherwise.
     */
    public function authenticate($credentials);
}
