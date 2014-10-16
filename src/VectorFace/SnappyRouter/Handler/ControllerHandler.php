<?php

namespace Vectorface\SnappyRouter\Handler;

use Vectorface\SnappyRouter\Encoder\NullEncoder;
use Vectorface\SnappyRouter\Request\HttpRequest as Request;
use Vectorface\SnappyRouter\Di\Di;

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

    private $routeParams;

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

        $pathComponents = array_map('trim', explode('/', $path));
        $pathComponentsCount = count($pathComponents);

        $controllerClass = 'index';
        $actionName = 'index';
        $this->routeParams = [];
        switch ($pathComponentsCount) {
            case 2:
                $actionName = $pathComponents[1];
                // fall through is intentional
            case 1:
                $controllerClass = $pathComponents[0];
                break;
            default:
                $controllerClass = $pathComponents[0];
                $actionName = $pathComponents[1];
                $this->routeParams = array_slice($pathComponents, 2);
        }

        if (empty($controllerClass)) {
            $controllerClass = 'index';
        }
        $controllerClass = ucfirst(strtolower(trim($controllerClass))).'Controller';
        $actionName = strtolower(trim($actionName)).'Action';

        $this->request = new Request(
            $controllerClass,
            $actionName,
            $verb
        );
        return true;
    }

    /**
     * Performs the actual routing.
     * @return Returns the result of the route.
     */
    public function performRoute()
    {
        $controller = null;
        $action = null;
        $this->determineControllerAndAction($controller, $action);

        var_dump($controller);

        /*
        $this->performServiceStep($activeHandler);
        $response = $this->performInvokeStep($activeHandler);
        http_response_code($response->getStatusCode());
        $responseString = $this->performEncodeStep($activeHandler, $response);
        return $responseString;
        */
    }

    private function determineControllerAndAction(&$controller, &$actionName)
    {
        $request = $this->getRequest();
        $this->invokePluginsHook(
            'beforeControllerSelected',
            array($this, $request)
        );

        $controllerDiKey = $request->getController();
        try {
            $controller = $this->getServiceProvider()->getServiceInstance($controllerDiKey);
            $actionName = $request->getAction();
            if (!method_exists($controller, $actionName)) {
                throw new HandlerExpection(sprintf(
                    '%s does not have method %s',
                    $controllerDiKey,
                    $actionName
                ));
            }
        } catch (Exception $e) {
            throw new HandlerExpection($e->getMessage());
        }

        $this->invokePluginsHook(
            'afterControllerSelected',
            array($this, $request, $controller, $actionName)
        );
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
