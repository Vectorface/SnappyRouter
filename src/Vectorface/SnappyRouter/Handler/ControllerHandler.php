<?php

namespace Vectorface\SnappyRouter\Handler;

use \Exception;
use Vectorface\SnappyRouter\Controller\AbstractController;
use Vectorface\SnappyRouter\Encoder\EncoderInterface;
use Vectorface\SnappyRouter\Encoder\NullEncoder;
use Vectorface\SnappyRouter\Encoder\TwigViewEncoder;
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
class ControllerHandler extends PatternMatchHandler
{
    /** Options key for the base path */
    const KEY_BASE_PATH = 'basePath';
    /** Options key for the view config */
    const KEY_VIEWS = 'views';
    /** Options key for the view path config */
    const KEY_VIEWS_PATH = 'path';

    /** The current web request */
    protected $request;
    /** The current encoder */
    protected $encoder;
    /** The current route parameters */
    protected $routeParams;

    /** Constants indicating the type of route */
    const DEFAULT_INDEX = 0;
    const CONTROLLER_FOUND = 1;
    const ACTION_FOUND = 2;
    const CONTROLLER_AND_ACTION_FOUND = 3;
    const PARAMS_FOUND = 4;
    const CONTROLLER_ACTION_AND_PARAMS_FOUND = 7;

    /** The valid routes the controller handler will match */
    private static $routes = array(
        '/' => self::DEFAULT_INDEX,
        '/{controller}' => self::CONTROLLER_FOUND,
        '/{controller}/' => self::CONTROLLER_FOUND,
        '/{controller}/{action}' => self::CONTROLLER_AND_ACTION_FOUND,
        '/{controller}/{action}/' => self::CONTROLLER_AND_ACTION_FOUND,
        '/{controller}/{action}/{params:.+}' => self::CONTROLLER_ACTION_AND_PARAMS_FOUND
    );

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
        $options = $this->getOptions();
        if (isset($options[self::KEY_BASE_PATH])) {
            $path = $this->extractPathFromBasePath($path, $options[self::KEY_BASE_PATH]);
        }
        // ensure the path has a leading slash
        if (empty($path) || $path[0] !== '/') {
            $path = '/'.$path;
        }

        // extract the controller, action and route parameters if present
        // and fall back to defaults when not present
        $controller = 'index';
        $action = 'index';
        $this->routeParams = array();
        $routeInfo = $this->getRouteInfo(self::$routes, $verb, $path);
        if ($routeInfo[1] & self::CONTROLLER_FOUND) {
            $controller = strtolower($routeInfo[2]['controller']);
            if ($routeInfo[1] & self::ACTION_FOUND) {
                $action = strtolower($routeInfo[2]['action']);
                if ($routeInfo[1] & self::PARAMS_FOUND) {
                    $this->routeParams = explode('/', $routeInfo[2]['params']);
                }
            }
        }

        $controllerClass = ucfirst($controller).'Controller';
        // ensure we actually handle the controller for this application
        try {
            $this->getServiceProvider()->getServiceInstance($controllerClass);
        } catch (Exception $e) {
            return false;
        }
        $actionName = $action.'Action';
        $this->configureViewEncoder($options, $controller, $action);

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
     * @return string Returns the result of the route.
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
     * @return HttpRequest Returns a Request object or null if this handler is not appropriate.
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
     * @return ControllerHandler Returns $this.
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

    /**
     * Configures the view encoder based on the current options.
     * @param array $options The current options.
     * @param string $controller The controller to use for the default view.
     * @param string $action The action to use for the default view.
     */
    private function configureViewEncoder($options, $controller, $action)
    {
        // configure the view encoder if they specify a view option
        if (isset($options[self::KEY_VIEWS])) {
            $this->encoder = new TwigViewEncoder(
                $options[self::KEY_VIEWS],
                sprintf('%s/%s.twig', $controller, $action)
            );
        } else {
            $this->encoder = new NullEncoder();
        }
    }
}
