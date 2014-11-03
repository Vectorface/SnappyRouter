<?php

namespace Vectorface\SnappyRouter\Authentication;

use \Closure;

/**
 * Authenticator implementation that leaves the authentication up to a Closure.
 *
 * For example:
 * \code{.php}
 * $auth = new CallbackAuthenticator(function($credentials) use ($externalAuth) {
 *     return $externalAuth->login($credentials['username'], $credentials['password']);
 * });
 * \endcode
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author J. Anderson <janderson@vectorface.com>
 */
class CallbackAuthenticator extends AbstractAuthenticator
{
    /**
     * Stores the callback to be used for authentication.
     *
     * @var Closure
     */
    private $callback;

    /**
     * Wrap another authentication mechanism via a callback.
     *
     * @param Closure $callback The callback, which is expected to have the same signature as $this->authenticate.
     */
    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Authenticate a set of credentials using a callback.
     *
     * @param mixed $credentials One or more credentials; A string password, or an array for multi-factor auth.
     * @return bool Returns true if the identity was authenticated, or false otherwise.
     */
    public function authenticate($credentials)
    {
        return (bool)call_user_func($this->callback, $credentials);
    }
}
