<?php

namespace Vectorface\SnappyRouter\Plugin\AccessControl;

use Vectorface\SnappyRouter\Controller\AbstractController;
use Vectorface\SnappyRouter\Exception\AccessDeniedException;
use Vectorface\SnappyRouter\Handler\AbstractHandler;
use Vectorface\SnappyRouter\Plugin\AbstractControllerPlugin;
use Vectorface\SnappyRouter\Request\HttpRequest;

/**
 * A plugin that adds appropriate content headers for http requests based on the response.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class CrossOriginRequestPlugin extends AbstractControllerPlugin
{
    /** the PHP constant for the Origin HTTP request header */
    const HEADER_CLIENT_ORIGIN = 'HTTP_ORIGIN';

    /** The HTTP header for allowing cross origin requests */
    const HEADER_ALLOW_ORIGIN      = 'Access-Control-Allow-Origin';
    /** The HTTP header for allowing a list of headers */
    const HEADER_ALLOW_HEADERS     = 'Access-Control-Allow-Headers';
    /** The HTTP header for allowing a list of methods */
    const HEADER_ALLOW_METHODS     = 'Access-Control-Allow-Methods';
    /** The HTTP header for allowing credentials */
    const HEADER_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';
    /** The HTTP header for the max age to cache access control */
    const HEADER_MAX_AGE           = 'Access-Control-Max-Age';

    /** the config key for the whitelist of allowed services */
    const CONFIG_SERVICE_WHITELIST = 'whitelist';
    /** the config key for the whitelist of allowed origin domains */
    const CONFIG_ORIGIN_WHITELIST = 'ignoreOrigins';
    /** the magic config option to allow all methods on a service */
    const CONFIG_ALL_METHODS   = 'all';

    /** A constant indicating how long (in seconds) a user agent should cache
       cross origin preflight response headers */
    const MAX_AGE = 86400; // 1 day

    // the array of allowed headers the user agent can send in a cross origin request
    private static $allowedHeaders = array(
        'accept', 'content-type'
    );

    // the array of allowed HTTP verbs that can be used to perform cross origin requests
    private static $allowedMethods = array(
        'GET', 'POST', 'OPTIONS'
    );

    /**
     * Invoked after the router has decided which controller will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param HttpRequest $request The request to be handled.
     * @param AbstractController $controller The controller determined to be used.
     * @param string $action The name of the action that will be invoked.
     */
    public function afterControllerSelected(
        AbstractHandler $handler,
        HttpRequest $request,
        AbstractController $controller,
        $action
    ) {
        parent::afterControllerSelected($handler, $request, $controller, $action);
        if (false === $this->isRequestCrossOrigin()) {
            // since the request isn't cross origin we don't need this plugin to
            // do any processing at all
            return;
        }

        $controller = $request->getController();
        if (false === $this->isServiceEnabledForCrossOrigin($controller, $action)) {
            // we have a cross origin request for a controller that's not enabled
            // so throw an exception instead of processing the request
            throw new AccessDeniedException(
                'Cross origin access denied to '.$controller.' and action '.$action
            );
        }

        // let the browser know this domain can make cross origin requests
        @header(self::HEADER_ALLOW_ORIGIN.': '.$_SERVER[self::HEADER_CLIENT_ORIGIN]);
        // do not explicitly block requests that pass a cookie
        @header(self::HEADER_ALLOW_CREDENTIALS.': true');

        // check the HTTP verb
        $httpVerb = strtoupper($request->getVerb());
        // if the request is for OPTIONS we include a couple of extra headers
        if ('OPTIONS' === $httpVerb) {
            // header for preflight cache expiry
            $maxAge = self::MAX_AGE;
            if (isset($this->options[self::HEADER_MAX_AGE])) {
                $maxAge = intval($this->options[self::HEADER_MAX_AGE]);
            }
            @header(self::HEADER_MAX_AGE.': '.$maxAge);

            // header for allowed request headers in cross origin requests
            $allowedHeaders = self::$allowedHeaders;
            if (isset($this->options[self::HEADER_ALLOW_HEADERS])) {
                $allowedHeaders = (array)$this->options[self::HEADER_ALLOW_HEADERS];
            }
            @header(self::HEADER_ALLOW_HEADERS.':'.implode(',', $allowedHeaders));

            // header for allowed HTTP methods in cross orgin requests
            $allowedMethods = self::$allowedMethods;
            if (isset($this->options[self::HEADER_ALLOW_METHODS])) {
                $allowedMethods = (array)$this->options[self::HEADER_ALLOW_METHODS];
            }
            @header(self::HEADER_ALLOW_METHODS.':'.implode(',', $allowedMethods));
        }
    }

    /**
     * Returns whether or not the current service/method combination is enabled
     * for cross origin requests.
     * @param string $service The service requested.
     * @param string $method The method requested.
     * @return Returns true if the service/method pair is in the whitelist and
     *         false otherwise.
     */
    private function isServiceEnabledForCrossOrigin($service, $method)
    {
        // ensure we have a whitelist and it is an array
        $whitelist = array();
        if (isset($this->options[self::CONFIG_SERVICE_WHITELIST])) {
            $whitelist = (array)$this->options[self::CONFIG_SERVICE_WHITELIST];
        }

        // ensure the service is listed in the whitelist
        if (!isset($whitelist[$service])) {
            return false;
        }
        // if the service is listed and set to the string 'all' this means all
        // methods are enabled for cross origin so we're good!
        if (is_string($whitelist[$service]) && self::CONFIG_ALL_METHODS === $whitelist[$service]) {
            return true;
        }

        $whitelistedServices = (is_array($whitelist[$service])) ? $whitelist[$service] : array();
        // ensure the method is listed in the list of services
        return in_array($method, $whitelistedServices);
    }

    /**
     * Returns true if the current requests is a cross origin request (i.e. does
     * the Origin HTTP header exist in the request) and false otherwise.
     * @return True if the request is cross origin and false otherwise.
     */
    private function isRequestCrossOrigin()
    {
        if (empty($_SERVER[self::HEADER_CLIENT_ORIGIN])) {
            return false;
        }

        $ignoredDomains = array();
        if (isset($this->options[self::CONFIG_ORIGIN_WHITELIST])) {
            $ignoredDomains = (array)$this->options[self::CONFIG_ORIGIN_WHITELIST];
        }
        return !in_array($_SERVER[self::HEADER_CLIENT_ORIGIN], $ignoredDomains);
    }
}
