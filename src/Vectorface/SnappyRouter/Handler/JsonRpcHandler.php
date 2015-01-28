<?php

namespace Vectorface\SnappyRouter\Handler;

use \stdClass;
use \Exception;
use Vectorface\SnappyRouter\Encoder\JsonEncoder;
use Vectorface\SnappyRouter\Exception\ResourceNotFoundException;
use Vectorface\SnappyRouter\Handler\AbstractRequestHandler;
use Vectorface\SnappyRouter\Request\HttpRequest;
use Vectorface\SnappyRouter\Request\JsonRpcRequest;
use Vectorface\SnappyRouter\Response\Response;
use Vectorface\SnappyRouter\Response\JsonRpcResponse;

/**
 * Handle JSON-RPC 1/2 requests
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 */
class JsonRpcHandler extends AbstractRequestHandler implements BatchRequestHandlerInterface
{
    /**
     * Option key for a base path
     */
    const KEY_BASE_PATH = 'basePath';

    /**
     * Error constants from the JSON-RPC 2.0 spec.
     */
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
     * An array of HttpRequest objects.
     *
     * @var \Vectorface\SnappyRouter\Request\HttpRequest
     */
    private $requests;


    /**
     * A flag indicating whether the request is a batch request.
     *
     * @var boolean
     */
    private $batch;

    /**
     * The encoder instance to be used to encode responses.
     *
     * @var \Vectorface\SnappyRouter\Encoder\EncoderInterface|\Vectorface\SnappyRouter\Encoder\JsonEncoder
     */
    private $encoder;

    /**
     * Returns true if the handler determines it should handle this request and false otherwise.
     *
     * @param string $path The URL path for the request.
     * @param array $query The query parameters.
     * @param array $post The post data.
     * @param string $verb The HTTP verb used in the request.
     * @return boolean Returns true if this handler will handle the request and false otherwise.
     */
    public function isAppropriate($path, $query, $post, $verb)
    {
        /* JSON-RPC is POST-only. */
        if ($verb !== 'POST') {
            return false;
        }

        /* Try decoding and validating POST data, and skip if it doesn't look like JSON-RPC */
        $post = json_decode(file_get_contents($this->stdin));
        if (!is_object($post) && !is_array($post)) {
            return false;
        }

        // extract the list of requests from the payload and tell the router
        // we'll handle this request
        $service = $this->getServiceFromPath($path);
        $this->processPayload($service, $post);
        return true;
    }

    /**
     * Determine the service to load via the ServiceProvider based on path
     *
     * @param string $path The raw path (URI) used to determine the service.
     * @return string The name of the service that we should attempt to load.
     */
    private function getServiceFromPath($path)
    {
        /* Ensure the path is in a standard form, removing empty elements. */
        $path = implode('/', array_filter(array_map('trim', explode('/', $path)), 'strlen'));

        /* If using the basePath option, strip the basePath. Otherwise, the path becomes the basename of the URI. */
        if (isset($this->options[self::KEY_BASE_PATH])) {
            $basePathPosition = strpos($path, $this->options[self::KEY_BASE_PATH]);
            if (false !== $basePathPosition) {
                $path = substr($path, $basePathPosition + strlen($this->options[self::KEY_BASE_PATH]));
            }

            return trim(dirname($path), "/") . '/' .  basename($path, '.php'); /* For example, x/y/z/FooService */
        } else {
            return basename($path, '.php'); /* For example: FooService, from /x/y/z/FooService.php */
        }
    }

    /**
     * Processes the payload POST data and sets up the array of requests.
     * @param string $service The service being requested.
     * @param array|object $post The raw POST data.
     */
    private function processPayload($service, $post)
    {
        $this->batch = is_array($post);
        if (false === $this->batch) {
            $post = array($post);
        }
        $this->requests = array_map(function ($payload) use ($service) {
            return new JsonRpcRequest($service, $payload);
        }, $post);
    }

    /**
     * Returns a request object extracted from the request details (path, query, etc). The method
     * isAppropriate() must have returned true, otherwise this method should return null.
     * @return HttpRequest Returns a Request object or null if this handler is not appropriate.
     */
    public function getRequest()
    {
        return (!empty($this->requests)) ? $this->requests[0] : null;
    }

    /**
     * Returns an array of batched requests.
     * @return An array of batched requests.
     */
    public function getRequests()
    {
        return $this->requests;
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

        /* Check if we can get the service. */
        try {
            $service = $this->getServiceProvider()->getServiceInstance(
                $this->getRequest()->getController()
            );
        } catch (Exception $e) {
            throw new ResourceNotFoundException(
                'No such service: '.$this->getRequest()->getController()
            );
        }

        /* Loop through each call in the possible batch. */
        $response = array();
        foreach ($this->requests as $request) {
            $callResponse = $this->invokeMethod($service, $request);

            /* Stop here if this isn't a batch. There is only one response. */
            if (false === $this->batch) {
                $response = $callResponse->getResponseObject();
                break;
            }

            /* Omit empty responses from the batch response. */
            if ($callResponse = $callResponse->getResponseObject()) {
                $response[] = $callResponse;
            }
        }

        @header('Content-type: application/json');
        $response = new Response($response);
        return $this->getEncoder()->encode($response);
    }

    /**
     * Invokes a method on a service class, based on the raw JSON-RPC request.
     *
     * @param mixed $service The service being invoked.
     * @param Vectorface\SnappyRouter\Request\JsonRpcRequest $request The request
     *        to invoke.
     * @return JsonRpcResponse A response based on the result of the procedure call.
     */
    private function invokeMethod($service, JsonRpcRequest $request)
    {
        if (false === $request->isValid()) {
            /* Note: Method isn't known, so invocation hooks aren't called. */
            return new JsonRpcResponse(
                null,
                new Exception(
                    'The JSON sent is not a valid Request object',
                    self::ERR_INVALID_REQUEST
                )
            );
        }

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
     *
     * @return \Vectorface\SnappyRouter\Encoder\EncoderInterface Returns the response encoder.
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
     * @return mixed Returns a serializable value that will be encoded and returned
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
