<?php

namespace Vectorface\SnappyRouter\Handler;

use Vectorface\SnappyRouter\Encoder\JsonEncoder;

/**
 * Handles REST-like URLs like '/api/v2/users/1/details'.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class RestHandler extends ControllerHandler
{
    /** The route parameter key for the API version */
    const KEY_API_VERSION = 'apiVersion';

    // the array of route parameters
    private $apiVersion;

    /**
     * Returns true if the handler determines it should handle this request and false otherwise.
     * @param string $path The URL path for the request.
     * @param array $query The query parameters.
     * @param array $post The post data.
     * @param string $verb The HTTP verb used in the request.
     * @return boolean Returns true if this handler will handle the request and false otherwise.
     */
    public function isAppropriate($path, $query, $post, $verb)
    {
        // remove the leading base path option if present
        if (isset($this->options[self::KEY_BASE_PATH])) {
            $path = $this->extractPathFromBasePath($path, $this->options[self::KEY_BASE_PATH]);
            unset($this->options[self::KEY_BASE_PATH]);
        }

        // extract the full path as components
        $pathComponents = array_filter(array_map('trim', explode('/', $path)), 'strlen');
        $path = implode('/', $pathComponents);

        $matches = array();
        if (preg_match('/^v((\d)+(\.(\d+))?)\/([a-zA-z]+)\/((\d)+)\/([a-zA-Z]+)$/', $path, $matches)) {
            // matches "/v{$version}/{$controller}/{$objectId}/${action}(/)"
            $this->apiVersion = $matches[1];
            return parent::isAppropriate(
                implode('/', array($matches[5], $matches[8], intval($matches[6]))),
                $query,
                $post,
                $verb
            );
        } elseif (preg_match('/^v((\d)+(\.(\d+))?)\/([a-zA-Z]+)\/((\d)+)$/', $path, $matches)) {
            // matches "/v{$version}/{$controller}/{$objectId}(/)"
            $this->apiVersion = $matches[1];
            return parent::isAppropriate(
                implode('/', array($matches[5], 'default', intval($matches[6]))),
                $query,
                $post,
                $verb
            );
        } elseif (preg_match('/^v((\d)+(\.(\d+))?)\/([a-zA-Z]+)\/([a-zA-Z]+)$/', $path, $matches)) {
            // matches "/v{$version}/{$controller}/{$action}(/)"
            $this->apiVersion = $matches[1];
            return parent::isAppropriate(
                implode('/', array($matches[5], $matches[6])),
                $query,
                $post,
                $verb
            );
        } elseif (preg_match('/^v((\d)+(\.(\d+))?)\/([a-zA-Z]+)$/', $path, $matches)) {
            // matches "/v{$version}/{$controller}(/)"
            $this->apiVersion = $matches[1];
            return parent::isAppropriate(
                implode('/', array($matches[5], 'default')),
                $query,
                $post,
                $verb
            );
        }
        return false;
    }

    /**
     * Performs the actual routing.
     * @return mixed Returns the result of the route.
     */
    public function performRoute()
    {
        $this->routeParams[self::KEY_API_VERSION] = $this->apiVersion;
        return parent::performRoute();
    }

    /**
     * Returns the active response encoder.
     * @return EncoderInterface Returns the response encoder.
     */
    public function getEncoder()
    {
        return new JsonEncoder();
    }
}
