<?php

namespace VectorFace\SnappyRouter\Handler;

use VectorFace\SnappyRouter\Request\HttpRequest as Request;

/*
use casino\engine\ServiceRouter\Mvc\Request;
use casino\engine\ServiceRouter\Encoder\ResponseEncoderInterface;
use casino\engine\ServiceRouter\Encoder\NullEncoder;
use casino\engine\ServiceRouter\Decoder\NullDecoder;
*/

/**
 * Handles MVC requests mapping URIs like /controller/action/param1/param2/...
 * to its corresponding controller action.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class ControllerHandler extends AbstractRequestHandler
{
    protected $request;
    protected $decoder;
    protected $encoder;

    /**
     * Returns true if the handler determines it should handle this request and false otherwise.
     * @param string $path The URL path for the request.
     * @param array $query The query parameters.
     * @param array $post The post data.
     * @param string $verb The HTTP verb used in the request.
     * @return Returns true if this handler will handle the request and false otherwise.
     */
    public function isAppropriate($path, $query, $post, $verb)
    {
        if (0 === strpos($path, '/')) {
            $path = substr($path, 1);
        }

        $pathComponents = explode('/', $path);
        $pathComponentsCount = count($pathComponents);

        $controller = 'index';
        $action = 'index';
        $params = [];
        switch ($pathComponentsCount) {
            case 2:
                $action = $pathComponents[1];
                // fall through is intentional
            case 1:
                $controller = $pathComponents[0];
                break;
            default:
                $controller = $pathComponents[0];
                $action = $pathComponents[1];
                $params = array_slice($pathComponents, 2);
        }

        if (empty($controller)) {
            $controller = 'index';
        }
        $controller = sprintf('%sController', ucfirst(trim($controller)));
        $action = sprintf('%sAction', strtolower(trim($action)));

        $this->request = new Request($controller, $action, $verb, $params, $_POST, $_GET);
        return true;
    }

    /**
     * Performs the actual routing.
     * @return Returns the result of the route.
     */
    public function performRoute()
    {
        $this->performServiceStep($activeHandler);
        $response = $this->performInvokeStep($activeHandler);
        http_response_code($response->getStatusCode());
        $responseString = $this->performEncodeStep($activeHandler, $response);
        return $responseString;
    }

    /**
     * Returns a request object extracted from the request details (path, query, etc). The method
     * isAppropriate() must have returned true, otherwise this method should return null.
     * @return Returns a Request object or null if this handler is not appropriate.
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the response encoder. By default, this will return a JSONResponseEncoder.
     * @return Returns an ResponseEncoderInterface object.
     */
    public function getEncoder()
    {
        if (isset($this->encoder)) {
            return $this->encoder;
        }

        $this->encoder = new NullEncoder();
        return $this->encoder;
    }

    /**
     * Sets the encoder to be used by this handler (overriding the default).
     * @param ResponseEncoderInterface $encoder The encoder to be used.
     * @return Returns $this.
     */
    public function setEncoder(ResponseEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        return $this;
    }

    /**
     * Returns the response decoder. By default, this will return a JSONRequestDecoder.
     * @return Returns an ResponseDecoderInterface object.
     */
    public function getDecoder()
    {
        if (isset($this->decoder)) {
            return $this->decoder;
        }

        $this->decoder = new NullDecoder();
        return $this->decoder;
    }

    /**
     * Sets the decoder to be used by this handler.
     * @param ResponseDecoderInterface $decoder The decoder to be used.
     * @return Returns $this.
     */
    public function setDecoder($decoder)
    {
        $this->decoder = $decoder;
        return $this;
    }
}
