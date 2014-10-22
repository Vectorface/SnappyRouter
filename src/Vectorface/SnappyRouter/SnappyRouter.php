<?php

namespace Vectorface\SnappyRouter;

use \Exception;
use Vectorface\SnappyRouter\Config\ConfigInterface;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Di\DiInterface;
use Vectorface\SnappyRouter\Exception\HandlerException;
use Vectorface\SnappyRouter\Exception\RouterExceptionInterface;
use Vectorface\SnappyRouter\Handler\AbstractHandler;
use Vectorface\SnappyRouter\Response\AbstractResponse;
use Vectorface\SnappyRouter\Response\Response;

/**
 * The main routing class that handles the full request.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class SnappyRouter
{
    /** the array key for configuring handlers */
    const KEY_HANDLERS = 'handlers';
    /** the array key for configuring the DI provider */
    const KEY_DI = 'di';

    private $config; // the configuration
    private $handlers; // array of registered handlers

    /**
     * The constructor for the service router.
     * @param array $config The configuration array.
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->parseConfig();
    }

    /**
     * Returns the array of registered handlers.
     * @return The array of registered handlers.
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * Handles the standard route. Determines the execution environment
     * and makes the appropriate call.
     * @param string $environment An optional environment variable, if not
     *        specified, the method will fallback to php_sapi_name().
     * @return string Returns the encoded response string.
     */
    public function handleRoute($environment = null)
    {
        if (null === $environment) {
            $environment = php_sapi_name();
        }
        switch ($environment) {
            case 'cli':
                break;
            default:
                $queryPos = strpos($_SERVER['REQUEST_URI'], '?');
                $path = (false === $queryPos) ? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], 0, $queryPos);
                return $this->handleHttpRoute(
                    $path,
                    $_GET,
                    $_POST,
                    isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET'
                );
        }
    }

    /**
     * Performs the actual request.
     * @param string $path The URL path from the client.
     * @param array $query The query parameters as an array.
     * @param string $post The raw post data as a string.
     * @param string $verb The HTTP verb used in the request.
     * @return string Returns an encoded string to pass back to the client.
     */
    public function handleHttpRoute($path, $query, $post, $verb)
    {
        $activeHandler = null;
        try {
            // determine which handler should handle this path
            $activeHandler = $this->determineHandler($path, $query, $post, $verb);
            // invoke the initial plugin hook
            $activeHandler->invokePluginsHook(
                'afterHandlerSelected',
                array($activeHandler)
            );
            $response = $activeHandler->performRoute();
            $activeHandler->invokePluginsHook(
                'afterFullRouteInvoked',
                array($activeHandler)
            );
            return $response;
        } catch (Exception $e) {
            if ($e instanceof RouterExceptionInterface) {
                http_response_code($e->getAssociatedStatusCode());
            } else {
                http_response_code(AbstractResponse::RESPONSE_SERVER_ERROR);
            }
            $responseString = $e->getMessage();
            if (null !== $activeHandler) {
                $activeHandler->invokePluginsHook(
                    'errorOccurred',
                    array($activeHandler, $e)
                );
                $responseString = $activeHandler->getEncoder()->encode(
                    new Response($activeHandler->handleException($e))
                );
            }
            return $responseString;
        }
    }

    /**
     * Performs the standard "handle" step of the routing process.
     * @param string $path The web request path.
     * @param array $query The array of query parameters.
     * @param array $post The array of post parameters.
     * @param string $verb The HTTP verb used in the request.
     * @return Returns the handler to be used for the route.
     */
    private function determineHandler($path, $query, $post, $verb)
    {
        // determine which handler should handle this path
        $activeHandler = null;
        foreach ($this->getHandlers() as $handler) {
            if (true === $handler->isAppropriate($path, $query, $post, $verb)) {
                return $handler;
            }
        }

        throw new HandlerException('No handler responded to request.');
    }

    // parses the passed in config file
    private function parseConfig()
    {
        // setup the DI layer
        $diClass = $this->config->get(self::KEY_DI);
        if (class_exists($diClass)) {
            $di = new $diClass();
            if ($di instanceof DiInterface) {
                Di::setDefault($di);
            }
        }
        $this->setupHandlers(
            $this->config->get(self::KEY_HANDLERS, array())
        );
    }

    // helper to setup the array of handlers
    private function setupHandlers($handlers)
    {
        $this->handlers = array();
        foreach ($handlers as $handlerClass => $handlerDetails) {
            $handlerInstance = null;
            $options = array();
            if (isset($handlerDetails[AbstractHandler::KEY_OPTIONS])) {
                $options = (array)$handlerDetails[AbstractHandler::KEY_OPTIONS];
            }

            if (isset($handlerDetails[AbstractHandler::KEY_CLASS])) {
                $handlerClass = $handlerDetails[AbstractHandler::KEY_CLASS];
            }

            if (!class_exists($handlerClass)) {
                throw new Exception(
                    'Cannot instantiate instance of '.$handlerClass
                );
            }
            $this->handlers[] = new $handlerClass($options);
        }
    }
}
