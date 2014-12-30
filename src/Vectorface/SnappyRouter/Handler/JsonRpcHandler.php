<?php

namespace Vectorface\SnappyRouter\Handler;

use \stdClass;
use \Exception;
use \EngineException;
use Vectorface\SnappyRouter\Encoder\JsonEncoder;
use Vectorface\SnappyRouter\Exception\RouterExceptionInterface;
use Vectorface\SnappyRouter\Handler\AbstractRequestHandler;
use Vectorface\SnappyRouter\Request\HttpRequest;
use Vectorface\SnappyRouter\Request\JsonRpcRequest;
use Vectorface\SnappyRouter\Response\Response;
use Vectorface\SnappyRouter\Response\JsonRpcResponse;

/**
 * Handle JSON-RPC 1/2 requests
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 *
 * Notes:
 *
 * On Mapping of URIs to services:
 *
 * The tailing end of a URI is mapped to a service, so:
 *  - A URI like /foo/bar/baz will match service key is bar/baz
 *  - A URI like /foo/bar/baz will not match a service key foo/bar, because the tailing element is missing.
 */
class JsonRpcHandler extends AbstractRequestHandler
{
    /**
     * Option key for a base path
     */
    const KEY_BASE_PATH = 'basePath';

    const ERR_PARSE_ERROR = -32700;
    const ERR_INVALID_REQUEST = -32600;
    const ERR_METHOD_NOT_FOUND = -32601;
    const ERR_INVALID_PARAMS = -32602;
    const ERR_INTERNAL_ERROR = -32603;

    /**
     * The path to the stdin stream. This can be set for testing.
     *
     * @var string
     */
    private $stdin = 'php://input';

    /**
     * The posted payload.
     *
     * @var array|object
     */
    private $payload;

    /**
     * A top-level HTTP request.
     *
     * @var Vectorface\SnappyRouter\Request\HttpRequest
     */
    private $request;

    /**
     * Returns true if the handler determines it should handle this request and false otherwise.
     *
     * @param string $path The URL path for the request.
     * @param array $query The query parameters.
     * @param array $post The post data.
     * @param string $verb The HTTP verb used in the request.
     * @return Returns true if this handler will handle the request and false otherwise.
     */
    public function isAppropriate($path, $query, $post, $verb)
    {
        /* JSON-RPC is POST-only. */
        if ($verb !== 'POST') {
            return false;
        }

        /* Ensure the path is in a standard form, removing empty elements. */
        $path = implode('/', array_filter(array_map('trim', explode('/', $path)), 'strlen'));

        /* If using the basePath option, strip the basePath. Otherwise, the path becomes the basename of the URI. */
        if (isset($this->options[self::KEY_BASE_PATH])) {
            $basePathPosition = strpos($path, $this->options[self::KEY_BASE_PATH]);
            if (false !== $basePathPosition) {
                $path = substr($path, $basePathPosition + strlen($this->options[self::KEY_BASE_PATH]));
            }

            $service = trim(dirname($path), "/") . '/' .  basename($path, '.php'); /* For example, x/y/z/FooService */
        } else {
            $service = basename($path, '.php'); /* For example: FooService, from /x/y/z/FooService.php */
        }

        /* Check if we can get the service. */
        try {
            $this->getServiceProvider()->getServiceInstance($service);
        } catch (Exception $e) {
            return false;
        }

        /* Try decoding and validating POST data, and skip if it doesn't look like JSON-RPC */
        $post = json_decode(file_get_contents($this->stdin));
        if (!is_object($post) && !is_array($post)) {
            return false;
        }

        /* Checks pass. Setup the request and tell the router that we'll handle this. */
        $this->payload = $post; //
        $this->request = new HttpRequest($service, null, $verb);
        return true;
    }

    /**
     * Returns a request object extracted from the request details (path, query, etc). The method
     * isAppropriate() must have returned true, otherwise this method should return null.
     * @return HttpRequest Returns a Request object or null if this handler is not appropriate.
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Performs the actual routing.
     *
     * @return string Returns the result of the route.
     */
    public function performRoute()
    {
        $this->invokePluginsHook(
            'beforeServiceSelected',
            array($this, $this->getRequest())
        );
        $service = $this->getServiceProvider()->getServiceInstance(
            $this->getRequest()->getController()
        );

        /* Possible JSON-RPC 2.0 batch */
        if (is_array($this->payload)) {
            $calls = $this->payload;
            $batch = true;
        } else {
            $calls = array($this->payload);
            $batch = false;
        }

        /* Loop through each call in the possible batch. */
        $response = array();
        foreach ($calls as $call) {
            $callRequest = $callResponse = null;
            try {
                $callRequest = new JsonRpcRequest($service, $call);
            } catch (Exception $e) {
                $callResponse = new JsonRpcResponse(null, $e);
            }

            /* If the request was parsed without an exception. */
            if (isset($callRequest) && !isset($callResponse)) {
                $callResponse = $this->invokeMethod($service, $callRequest);
            }


            if ($batch) {
                /* Omit empty responses from the batch response. */
                if ($callResponse = $callResponse->getResponseObject()) {
                    $response[] = $callResponse;
                }
            } else {
                $response = $callResponse->getResponseObject();
                break;
            }
        }

        @header('Content-type: application/json');
        $response = new Response($response);
        return $this->getEncoder()->encode($response);
    }

    private function invokeMethod($service, JsonRpcRequest $request)
    {
        $action = $request->getAction();
        $this->invokePluginsHook(
            'afterServiceSelected',
            array($this, $request, $service, $action)
        );

        $this->invokePluginsHook(
            'beforeMethodInvoked',
            array($this, $request, $service, $action)
        );

        try {
            $response = new JsonRpcResponse(
                call_user_func_array(array($service, $action), $request->getParameters()),
                null,
                $request
            );
        } catch (Exception $e) {
            $error = new Exception($e->getMessage(), self::ERR_INTERNAL_ERROR);
            $response = new JsonRpcResponse(null, $error, $request);
        }

        $this->invokePluginsHook(
            'afterMethodInvoked',
            array($this, $request, $service, $action, $response)
        );

        return $response;
    }

    /**
     * Returns the active response encoder.
     * @return EncoderInterface Returns the response encoder.
     */
    public function getEncoder()
    {
        if (!isset($this->encoder)) {
            $this->encoder = new JsonEncoder();
        }
        return $this->encoder;
    }

    /**
     * Provides the handler with an opportunity to perform any last minute
     * error handling logic. The returned value will be serialized by the
     * handler's encoder.
     *
     * @param Exception $e The exception that was thrown.
     * @return Returns a serializable value that will be encoded and returned
     *         to the client.
     */
    public function handleException(Exception $e)
    {
        if ($e->getCode() > -32000) {
            /* Don't pass through internal errors in case there's something sensitive. */
            $response = new JsonRpcResponse(null, new Exception("Internal Error", self::ERR_INTERNAL_ERROR));
        } else {
            /* JSON-RPC errors (<= -32000) can be passed on. */
            $response = new JsonRpcResponse(null, $e, null);
        }

        return $response->getResponseObject();
    }
}
