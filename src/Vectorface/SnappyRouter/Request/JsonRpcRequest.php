<?php

namespace Vectorface\SnappyRouter\Request;

use \Exception;
use \Vectorface\SnappyRouter\Handler\JsonRpcHandler;

/**
 * A class representing a JSON-RPC request.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 */
class JsonRpcRequest extends HttpRequest
{
    /**
     * Holds the JSON-RPC request payload.
     *
     * @var Object
     */
    private $payload;

    /**
     * Constructor for a request.
     *
     * @param string $controller The controller being requested.
     * @param object $payload The action being invoked.
     */
    public function __construct($controller, $payload)
    {
        // extract a minimal "action" value from the payload
        $action = '';
        if (is_object($payload) && isset($payload->method)) {
            $action = $payload->method;
        }
        parent::__construct($controller, $action, 'POST');
        $this->payload = $payload;
    }

    /**
     * Returns the POST data parameter associated with the specified key.
     *
     * Since JSON-RPC and POST'ed data are mutually exclusive this returns null, or the default if provided.
     *
     * @param string $param The POST data parameter to retrieve.
     * @param mixed $defaultValue The default value to use when the key is not present.
     * @param mixed $filters The array of filters (or single filter) to apply to the data. Ignored.
     * @return mixed Returns null because POST is not possible, or the default value if the parameter is not present.
     */
    public function getPost($param, $defaultValue = null, $filters = array())
    {
        return isset($defaultValue) ? $defaultValue : null;
    }

    /**
     * Get the request version.
     *
     * @return string The request's version string. "1.0" is assumed if version is not present in the request.
     */
    public function getVersion()
    {
        return isset($this->payload->jsonrpc) ? $this->payload->jsonrpc : "1.0";
    }

    /**
     * Get the request method.
     *
     * @return string The request method name.
     */
    public function getMethod()
    {
        return $this->payload->method;
    }

    /**
     * Get the request identifier
     *
     * @return mixed The request identifier. This is generally a string, but the JSON-RPC spec isn't strict.
     */
    public function getIdentifier()
    {
        return isset($this->payload->id) ? $this->payload->id : null;
    }

    /**
     * Get request parameters.
     *
     * Note: Since PHP does not support named params, named params are turned into a single request object parameter.
     *
     * @return array An array of request paramters
     */
    public function getParameters()
    {
        if (isset($this->payload->params)) {
            /* JSON-RPC 2 can pass named params. For PHP's sake, turn that into a single object param. */
            return is_array($this->payload->params) ? $this->payload->params : array($this->payload->params);
        }
        return array();
    }

    /**
     * Returns whether this request is minimally valid for JSON RPC.
     * @return Returns true if the payload is valid and false otherwise.
     */
    public function isValid()
    {
        $action = $this->getAction();
        return is_object($this->payload) && !empty($action);
    }
}
