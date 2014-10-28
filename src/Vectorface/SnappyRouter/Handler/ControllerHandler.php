<?php

namespace Vectorface\SnappyRouter\Handler;

use \Exception;
use Vectorface\SnappyRouter\Controller\AbstractController;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Encoder\EncoderInterface;
use Vectorface\SnappyRouter\Encoder\NullEncoder;
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
        // remove the leading base path option if present
        if (isset($this->options[self::KEY_BASE_PATH])) {
            $pos = strpos($path, $this->options[self::KEY_BASE_PATH]);
            if (false !== $pos) {
                $path = substr($path, $pos + strlen($this->options[self::KEY_BASE_PATH]));
            }
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
        $controllerClass = ucfirst(strtolower(trim($controllerClass))).'Controller';
        $actionName = strtolower(trim($actionName)).'Action';

        // ensure we actually handle the controller for this application
        try {
            $this->getServiceProvider()->getServiceInstance($controllerClass);
        } catch (Exception $e) {
            return false;
        }

        $this->request = new HttpRequest(
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
        // pass the various configurations to the DI layer
        $viewConfig = isset($this->options[ControllerHandler::KEY_VIEWS]) ?
            (array) $this->options[ControllerHandler::KEY_VIEWS] : array();
        $this->set(ControllerHandler::KEY_VIEWS, $viewConfig);

        if (isset($this->options[self::KEY_BASE_PATH])) {
            $this->set(self::KEY_BASE_PATH, $this->options[self::KEY_BASE_PATH]);
        }

        $controller = null;
        $action = null;
        $this->determineControllerAndAction($controller, $action);
        $response = $this->invokeControllerAction($controller, $action);
        \Vectorface\SnappyRouter\http_response_code($response->getStatusCode());
        return $this->getEncoder()->encode($response);
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

        // generate a default view by convention
        $controllerName = substr($controllerDiKey, 0, strlen($controllerDiKey) - 10);
        $viewActionName = substr($actionName, 0, strlen($actionName) - 6);
        $defaultView = sprintf(
            '%s/%s.twig',
            strtolower($controllerName),
            strtolower($viewActionName)
        );
        $controller->initialize($request, $defaultView);
    }

    /**
     * Invokes the actual controller action and returns the response.
     * @param AbstractController $controller The controller to use.
     * @param string $action The action to invoke.
     * @return AbstractResponse Returns the response from the action.
     */
    private function invokeControllerAction(AbstractController $controller, $action)
    {
        $this->invokePluginsHook(
            'beforeActionInvoked',
            array($this, $this->getRequest(), $controller, $action)
        );
        $response = $controller->$action($this->routeParams);
        // returning null in the method is the same as returning an empty array
        if (null === $response) {
            $response = array();
        }
        // merge the existing array with the view environment variables and
        // render the default view
        if (is_array($response)) {
            $response = $controller->renderView($response);
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
        if (isset($this->encoder)) {
            return $this->encoder;
        }

        $this->encoder = new NullEncoder();
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
}
