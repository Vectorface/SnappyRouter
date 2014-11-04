<?php

namespace Vectorface\SnappyRouter;

use \Exception;
use Vectorface\SnappyRouter\Config\ConfigInterface;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Di\DiInterface;
use Vectorface\SnappyRouter\Exception\ResourceNotFoundException;
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
     * @param string $environment (optional) An optional environment variable, if not
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
                $components = empty($_SERVER['argv']) ? array() : $_SERVER['argv'];
                return $this->handleCliRoute($components).PHP_EOL;
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
     * Handles routing an HTTP request directly.
     * @param string $path The URL path from the client.
     * @param array $query The query parameters as an array.
     * @param array $post The post data as an array.
     * @param string $verb The HTTP verb used in the request.
     * @return string Returns an encoded string to pass back to the client.
     */
    public function handleHttpRoute($path, $query, $post, $verb)
    {
        return $this->invokeHandler(false, array($path, $query, $post, $verb));
    }

    /**
     * Handles routing a CLI request directly.
     * @param array $pathComponents The array of path components to the CLI script.
     * @return string Returns an encoded string to be output to the CLI.
     */
    public function handleCliRoute($pathComponents)
    {
        return $this->invokeHandler(true, array($pathComponents));
    }

    /**
     * Determines which handler is appropriate for this request.
     *
     * @param bool $isCli True for CLI handlers, false otherwise.
     * @param array $handlerParams An array parameters required by the handler.
     * @return Returns the handler to be used for the route.
     */
    private function invokeHandler($isCli, $handlerParams)
    {
        $activeHandler = null;
        try {
            // determine which handler should handle this path
            $activeHandler = $this->determineHandler($isCli, $handlerParams);
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
            // if we have a valid handler give it a chance to handle the error
            if (null !== $activeHandler) {
                $activeHandler->invokePluginsHook(
                    'errorOccurred',
                    array($activeHandler, $e)
                );
                return $activeHandler->getEncoder()->encode(
                    new Response($activeHandler->handleException($e))
                );
            }

            // if not on the command line, set an HTTP response code
            if (!$isCli) {
                $responseCode = AbstractResponse::RESPONSE_SERVER_ERROR;
                if ($e instanceof RouterExceptionInterface) {
                    $responseCode = $e->getAssociatedStatusCode();
                }
                \Vectorface\SnappyRouter\http_response_code($responseCode);
            }
            return $e->getMessage();
        }
    }

    /**
     * Determines which handler is appropriate for this request.
     *
     * @param bool $isCli True for CLI handlers, false otherwise.
     * @param array $checkParams An array parameters for the handler isAppropriate method.
     * @return Returns the handler to be used for the route.
     */
    private function determineHandler($isCli, $checkParams)
    {
        // determine which handler should handle this path
        foreach ($this->getHandlers() as $handler) {
            if ($isCli !== $handler->isCliHandler()) {
                continue;
            }
            $callback = array($handler, 'isAppropriate');
            if (true === call_user_func_array($callback, $checkParams)) {
                return $handler;
            }
        }

        throw new ResourceNotFoundException($isCli ? 'No CLI handler registered.' : '');
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
