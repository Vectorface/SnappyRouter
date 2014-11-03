<?php

namespace Vectorface\SnappyRouter\Handler;

use \Exception;
use Vectorface\SnappyRouter\Controller\AbstractController;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Encoder\EncoderInterface;
use Vectorface\SnappyRouter\Encoder\NullEncoder;
use Vectorface\SnappyRouter\Encoder\TwigViewEncoder;
use Vectorface\SnappyRouter\Exception\HandlerException;
use Vectorface\SnappyRouter\Exception\ResourceNotFoundException;
use Vectorface\SnappyRouter\Request\HttpRequest;
use Vectorface\SnappyRouter\Response\AbstractResponse;
use Vectorface\SnappyRouter\Response\Response;

/**
 * Handles MVC requests mapping URIs like /controller/action/param1/param2/...
 * to its corresponding controller action.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class ControllerHandler extends AbstractRequestHandler
{

    const KEY_BASE_PATH = 'basePath';
    const KEY_VIEWS = 'views';
    const KEY_VIEWS_PATH = 'path';

    protected $request;
    protected $decoder;
    protected $encoder;

    protected $routeParams;

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
        // remove the leading base path option if present
        if (isset($this->options[self::KEY_BASE_PATH])) {
            $path = $this->extractPathFromBasePath($path, $this->options[self::KEY_BASE_PATH]);
        }

        // remove the leading /
        if (0 === strpos($path, '/')) {
            $path = substr($path, 1);
        }

        // split the path components to find the controller, action and route parameters
        $pathComponents = array_filter(array_map('trim', explode('/', $path)), 'strlen');
        $pathComponentsCount = count($pathComponents);

        // default values if not present
        $controllerClass = 'index';
        $actionName = 'index';
        $this->routeParams = array();
        switch ($pathComponentsCount) {
            case 0:
                break;
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
        $controllerClass = strtolower($controllerClass);
        $actionName = strtolower($actionName);
        $defaultView = sprintf(
            '%s/%s.twig',
            $controllerClass,
            $actionName
        );
        $controllerClass = ucfirst($controllerClass).'Controller';
        $actionName = $actionName.'Action';

        // ensure we actually handle the controller for this application
        try {
            $this->getServiceProvider()->getServiceInstance($controllerClass);
        } catch (Exception $e) {
            return false;
        }

        // configure the view encoder if they specify a view option
        if (isset($this->options[self::KEY_VIEWS])) {
            $this->encoder = new TwigViewEncoder(
                $this->options[self::KEY_VIEWS],
                $defaultView
            );
        } else {
            $this->encoder = new NullEncoder();
        }

        // configure the request object
        $this->request = new HttpRequest(
            $controllerClass,
            $actionName,
            $verb
        );
        $this->request->setQuery($query);
        $this->request->setPost($post);

        // return that we will handle this request
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
        $response = $this->invokeControllerAction($controller, $action);
        \Vectorface\SnappyRouter\http_response_code($response->getStatusCode());
        return $this->getEncoder()->encode($response);
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
     * Returns the active response encoder.
     * @return EncoderInterface Returns the response encoder.
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * Sets the encoder to be used by this handler (overriding the default).
     * @param EncoderInterface $encoder The encoder to be used.
     * @return Returns $this.
     */
    public function setEncoder(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        return $this;
    }

    /**
     * Returns the new path with the base path extracted.
     * @param string $path The full path.
     * @param string $basePath The base path to extract.
     * @return string Returns the new path with the base path removed.
     */
    protected function extractPathFromBasePath($path, $basePath)
    {
        $pos = strpos($path, $basePath);
        return (false === $pos) ? $path : substr($path, $pos + strlen($basePath));
    }

    /**
     * Determines the exact controller instance and action name to be invoked
     * by the request.
     * @param mixed $controller The controller passed by reference.
     * @param mixed $actionName The action name passed by reference.
     */
    private function determineControllerAndAction(&$controller, &$actionName)
    {
        $request = $this->getRequest();
        $this->invokePluginsHook(
            'beforeControllerSelected',
            array($this, $request)
        );

        $controllerDiKey = $request->getController();
        $controller = $this->getServiceProvider()->getServiceInstance($controllerDiKey);
        $actionName = $request->getAction();
        if (!method_exists($controller, $actionName)) {
            throw new ResourceNotFoundException(sprintf(
                '%s does not have method %s',
                $controllerDiKey,
                $actionName
            ));
        }
        $this->invokePluginsHook(
            'afterControllerSelected',
            array($this, $request, $controller, $actionName)
        );
        $controller->initialize($request, $this);
    }

    /**
     * Invokes the actual controller action and returns the response.
     * @param AbstractController $controller The controller to use.
     * @param string $action The action to invoke.
     * @return AbstractResponse Returns the response from the action.
     */
    protected function invokeControllerAction(AbstractController $controller, $action)
    {
        $this->invokePluginsHook(
            'beforeActionInvoked',
            array($this, $this->getRequest(), $controller, $action)
        );
        $response = $controller->$action($this->routeParams);
        if (null === $response) {
            // if the action returns null, we simply render the default view
            $response = array();
        } elseif (!is_string($response)) {
            // if they don't return a string, try to use whatever is returned
            // as variables to the view renderer
            $response = (array)$response;
        }

        // merge the response variables with the existing view context
        if (is_array($response)) {
            $response = array_merge($controller->getViewContext(), $response);
        }

        // whatever we have as a response needs to be encapsulated in an
        // AbstractResponse object
        if (!($response instanceof AbstractResponse)) {
            $response = new Response($response);
        }
        $this->invokePluginsHook(
            'afterActionInvoked',
            array($this, $this->getRequest(), $controller, $action, $response)
        );
        return $response;
    }
}
