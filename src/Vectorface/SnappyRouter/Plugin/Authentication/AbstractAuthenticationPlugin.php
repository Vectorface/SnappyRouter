<?php

namespace Vectorface\SnappyRouter\Plugin\Authentication;

use Vectorface\SnappyRouter\Authentication\AuthenticatorInterface;
use Vectorface\SnappyRouter\Exception\UnauthorizedException;
use Vectorface\SnappyRouter\Exception\InternalErrorException;
use Vectorface\SnappyRouter\Handler\AbstractHandler;
use Vectorface\SnappyRouter\Plugin\AbstractPlugin;

/**
 * An abstract plugin for extracting authentication information and running an authentication callback.
 *
 * The extraction of authentication information is left up to the subclass.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author J. Anderson <janderson@vectorface.com>
 * @author Dan Bruce   <dbruce@vectorface.com>
 */
abstract class AbstractAuthenticationPlugin extends AbstractPlugin
{
    /**
     * The default dependency injection key for the authentication mechanism.
     *
     * This also serves as the name of the DI key option, for consistency.
     */
    const DI_KEY_AUTH = 'AuthMechanism';

    /** The key for fetching the authentication mechanism from dependency
        injection. */
    protected $authKey = self::DI_KEY_AUTH;

    /**
     * Constructor for the class.
     *
     * @param array $options An array of options for the plugin.
     */
    public function __construct($options)
    {
        parent::__construct($options);

        if (isset($options[self::DI_KEY_AUTH])) {
            $this->authKey = $options[self::DI_KEY_AUTH];
        }
    }

    /**
     * Invoked directly after the router decides which handler will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     */
    public function afterHandlerSelected(AbstractHandler $handler)
    {
        parent::afterHandlerSelected($handler);

        $auth = $this->get($this->authKey);
        if (!($auth instanceof AuthenticatorInterface)) {
            throw new InternalErrorException(sprintf(
                "Implementation of AuthenticationInterface required. Please check your %s configuration.",
                $this->authKey
            ));
        }

        if (!($credentials = $this->getCredentials())) {
            throw new UnauthorizedException("Authentication is required to access this resource.");
        }

        if (!$auth->authenticate($credentials)) {
            throw new UnauthorizedException("Authentication is required to access this resource.");
        }
    }

    /**
     * Extract credentials from the request.
     *
     * @return mixed An array of credentials; A username and password pair, or false if credentials aren't available
     */
    abstract public function getCredentials();
}
