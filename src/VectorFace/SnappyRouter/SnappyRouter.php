<?php

namespace VectorFace\SnappyRouter;

use \Exception;

use VectorFace\SnappyRouter\Config\ConfigInterface;
use VectorFace\SnappyRouter\Di\Di;
use VectorFace\SnappyRouter\Di\DiInterface;
use VectorFace\SnappyRouter\Exception\HandlerException;
use VectorFace\SnappyRouter\Exception\RouterExceptionInterface;
use VectorFace\SnappyRouter\Handler\AbstractHandler;
use VectorFace\SnappyRouter\Response\AbstractResponse;
use VectorFace\SnappyRouter\Response\Response;

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

        // setup the DI layer
        $diClass = $this->config->get(self::KEY_DI);
        if (class_exists($diClass)) {
            $di = new $diClass();
            if ($di instanceof DiInterface) {
                Di::setDefault($di);
            }
        }
        $this->parseConfig($this->config);
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
     * Sets the array of registered handlers.
     * @param array $handlers A new array of handlers.
     * @return Returns $this.
     */
    public function setHandlers($handlers)
    {
        $this->handlers = $handlers;
        return $this;
    }

    /**
     * Performs the actual routing. Determines whether we are running in a web
     * environment or on the CLI and invokes the appropriate path.
     */
    public function handleRoute($sapi = null)
    {
        $sapi = is_string($sapi) ? $sapi : php_sapi_name();
        switch ($sapi) {
            case 'cli':
                return;
            default:
                return;
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
            return $activeHandler->performRoute();
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
     * Handles a CLI route.
     * @param array $args The array of CLI arguments.
     */
    /*
    public function handleCLIRoute($args)
    {
        // determine which handler should handle this path
        $activeHandler = $this->performCLIHandleStep($args);
        if (null === $activeHandler) {
            throw new InvalidServiceRouterHandlerException(
                'Unable to find suitable handler for CLI route.'
            );
        }
        $request = $activeHandler->getRequest();
        $taskName = $request->getService();
        if (!isset($this->tasks[$taskName])) {
            throw new \Exception('No task registered for '.$taskName);
        }
        $task = new $this->tasks[$taskName];
        if ($task instanceof BaseTask) {
            $task->init($this->config);
        }
        $action = $request->getMethod();
        $task->$action($request->getArguments());
    }
    */

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
    private function parseConfig($config)
    {
        $handlers = $this->extractConfigArray($config, self::KEY_HANDLERS);
        $this->setupHandlers($handlers);
    }

    // ensures we always get an array out of $config[$key]
    private function extractConfigArray($config, $key)
    {
        if (!isset($config[$key])) {
            return array();
        }

        if (is_array($config[$key])) {
            return $config[$key];
        } elseif (is_object($config[$key])) {
            return (array)$config[$key];
        } else {
            return array();
        }
    }

    // helps to setup the array of handlers
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

            if (isset($handlerDetails[AbstractHandler::KEY_FILE])) {
                require_once $handlerDetails[AbstractHandler::KEY_FILE];
            }

            if (!class_exists($handlerClass)) {
                throw new Exception(
                    'Cannot instantiate instance of '.$handlerDetails[AbstractHandler::KEY_CLASS]
                );
            }
            $this->handlers[] = new $handlerClass($options);
        }
    }

    // filters out the list of plugins that may not be compatible with the given
    // service and method based on the plugin's own blacklist/whitelist
    /*
    private function filterPluginsForServiceAndMethod($service, $method)
    {
        $pluginsList = [];
        foreach ($this->getPlugins() as $plugin) {
            if ($plugin->supportsServiceAndMethod($service, $method)) {
                $pluginsList[] = $plugin;
            }
        }
        $this->plugins = $pluginsList;
    }
    */
}
