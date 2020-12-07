<?php

namespace Vectorface\SnappyRouter\Plugin\AccessControl;

use Vectorface\SnappyRouter\Exception\AccessDeniedException;
use Vectorface\SnappyRouter\Exception\InternalErrorException;
use Vectorface\SnappyRouter\Handler\AbstractHandler;
use Vectorface\SnappyRouter\Handler\AbstractRequestHandler;
use Vectorface\SnappyRouter\Handler\BatchRequestHandlerInterface;
use Vectorface\SnappyRouter\Plugin\AbstractPlugin;

/**
 * A plugin that adds appropriate content headers for http requests based on the response.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class CrossOriginRequestPlugin extends AbstractPlugin
{
    /** the PHP constant for the Origin HTTP request header */
    const HEADER_CLIENT_ORIGIN = 'HTTP_ORIGIN';

    /** The HTTP header for allowing cross origin requests */
    const HEADER_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    /** The HTTP header for allowing a list of headers */
    const HEADER_ALLOW_HEADERS = 'Access-Control-Allow-Headers';
    /** The HTTP header for allowing a list of methods */
    const HEADER_ALLOW_METHODS = 'Access-Control-Allow-Methods';
    /** The HTTP header for allowing credentials */
    const HEADER_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';
    /** The HTTP header for the max age to cache access control */
    const HEADER_MAX_AGE = 'Access-Control-Max-Age';

    /** the config key for the whitelist of allowed services */
    const CONFIG_SERVICE_WHITELIST = 'whitelist';
    /** the config key for the whitelist of allowed origin domains */
    const CONFIG_ORIGIN_WHITELIST = 'ignoreOrigins';
    /** the magic config option to allow all methods on a service */
    const CONFIG_ALL_METHODS = 'all';

    /** A constant indicating how long (in seconds) a user agent should cache
       cross origin preflight response headers */
    const MAX_AGE = 86400; // 1 day

    // the array of allowed headers the user agent can send in a cross origin request
    private static $allowedHeaders = [
        'accept', 'content-type'
    ];

    // the array of allowed HTTP verbs that can be used to perform cross origin requests
    private static $allowedMethods = [
        'GET', 'POST', 'OPTIONS'
    ];

    /**
     * Invoked directly after the router decides which handler will be used.
     *
     * @param AbstractHandler $handler The handler selected by the router.
     * @throws AccessDeniedException
     * @throws InternalErrorException
     */
    public function afterHandlerSelected(AbstractHandler $handler)
    {
        parent::afterHandlerSelected($handler);
        if (false === $this->isRequestCrossOrigin() || !($handler instanceof AbstractRequestHandler)) {
            // since the request isn't cross origin we don't need this plugin to
            // do any processing at all
            return;
        }

        $request = $handler->getRequest();
        if (null === $request) {
            // the CORS plugin only supports handlers with standard requests
            return;
        }
        $requests = [$request];
        if ($handler instanceof BatchRequestHandlerInterface) {
            $requests = $handler->getRequests();
        }
        $this->processRequestsForAccessDenial($requests);

        // let the browser know this domain can make cross origin requests
        @header(self::HEADER_ALLOW_ORIGIN.': '.$_SERVER[self::HEADER_CLIENT_ORIGIN]);
        // do not explicitly block requests that pass a cookie
        @header(self::HEADER_ALLOW_CREDENTIALS.': true');

        if ('OPTIONS' === strtoupper($request->getVerb())) {
            $this->addHeadersForOptionsRequests();
        }
    }

    /**
     * Processes the list of requests to check if any should be blocked due
     * to CORS policy.
     *
     * @param array $requests The array of requests.
     * @throws AccessDeniedException
     * @throws InternalErrorException
     */
    private function processRequestsForAccessDenial(array $requests)
    {
        foreach ($requests as $request) {
            $controller = $request->getController();
            $action = $request->getAction();
            if (false === $this->isServiceEnabledForCrossOrigin($controller, $action)) {
                // we have a cross origin request for a controller that's not enabled
                // so throw an exception instead of processing the request
                throw new AccessDeniedException(
                    'Cross origin access denied to '.$controller.' and action '.$action
                );
            }
        }
    }

    /**
     * Adds additional headers for the OPTIONS http verb.
     */
    private function addHeadersForOptionsRequests()
    {
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

    /**
     * Returns whether or not the current service/method combination is enabled
     * for cross origin requests.
     *
     * @param string $service The service requested.
     * @param string|null $method The method requested.
     * @return boolean Returns true if the service/method pair is in the whitelist and
     *         false otherwise.
     * @throws InternalErrorException
     */
    protected function isServiceEnabledForCrossOrigin($service, $method)
    {
        // ensure the plugin has a valid whitelist
        if (!isset($this->options[self::CONFIG_SERVICE_WHITELIST])) {
            throw new InternalErrorException(
                'Cross origin request plugin missing whitelist.'
            );
        }

        $whitelist = $this->options[self::CONFIG_SERVICE_WHITELIST];
        // check if the whitelist is the string "all"
        if (self::CONFIG_ALL_METHODS === $whitelist) {
            return true;
        }

        // ensure the whitelist is an array and the service is listed within
        // the whitelist
        if (!is_array($whitelist) || !isset($whitelist[$service])) {
            return false;
        }

        // if the service is listed and set to the string 'all' this means all
        // methods are enabled for cross origin so we're good!
        if (self::CONFIG_ALL_METHODS === $whitelist[$service] || null === $method) {
            return true;
        }

        $whitelistedServices = (is_array($whitelist[$service])) ? $whitelist[$service] : [];
        // ensure the method is listed in the list of services
        return in_array($method, $whitelistedServices);
    }

    /**
     * Returns true if the current requests is a cross origin request (i.e. does
     * the Origin HTTP header exist in the request) and false otherwise.
     * @return boolean Returns true if the request is cross origin and false otherwise.
     */
    protected function isRequestCrossOrigin()
    {
        if (empty($_SERVER[self::HEADER_CLIENT_ORIGIN])) {
            return false;
        }

        $ignoredDomains = [];
        if (isset($this->options[self::CONFIG_ORIGIN_WHITELIST])) {
            $ignoredDomains = (array)$this->options[self::CONFIG_ORIGIN_WHITELIST];
        }
        return !in_array($_SERVER[self::HEADER_CLIENT_ORIGIN], $ignoredDomains);
    }
}
