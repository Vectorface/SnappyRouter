<?php

namespace VectorFace\SnappyRouter;

use \Exception;

use VectorFace\SnappyRouter\Config\ConfigInterface;
use VectorFace\SnappyRouter\Di\Di;
use VectorFace\SnappyRouter\Di\DiInterface;
use VectorFace\SnappyRouter\Exception\RouterExceptionInterface;
use VectorFace\SnappyRouter\Response\AbstractResponse;

/**
 * The main routing class that handles the full request.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class SnappyRouter
{
    /** the array key for configuring handlers */
    const KEY_HANDLERS = 'handlers';
    /** the array key for configuring plugins */
    const KEY_PLUGINS = 'plugins';
    /** the array key for configuring the service provider */
    const KEY_SERVICES = 'services';
    /** the array key for configuring the DI provider */
    const KEY_DI = 'di';
    /** the array key for configuring CLI tasks */
    const KEY_TASKS = 'tasks';

    private $config; // the configuration
    private $handlers; // array of registered handlers
    private $plugins; // array of registered plugins
    private $serviceProvider; // the current service provider
    private $tasks; // an array of available CLI tasks

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
     * Returns the array of registered plugins.
     * @return The array of registered plugins.
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * Sets the array of registered plugins.
     * @param array $plugins A new array of plugins.
     * @return Returns $this.
     */
    public function setPlugins($plugins)
    {
        $this->plugins = $this->sortPlugins($plugins);
        return $this;
    }

    /**
     * Returns the service provider to be used by the router.
     * @return The service provider to be used by the router.
     */
    public function getServiceProvider()
    {
        return $this->serviceProvider;
    }

    /**
     * Sets the service provider to be used by the router.
     * @param ServiceProvider $serviceProvider The service provider to be used by the router.
     * @return Returns $this.
     */
    public function setServiceProvider($serviceProvider)
    {
        $this->serviceProvider = $serviceProvider;
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
            return $activeHandler->performRoute();
            /*
            */
        } catch (Exception $e) {
            if ($e instanceof RouterExceptionInterface) {
                http_response_code($e->getAssociatedStatusCode());
            } else {
                http_response_code(Response\AbstractResponse::RESPONSE_SERVER_ERROR);
            }
            $this->invokePluginsHook('onError', array(
                $activeHandler,
                $this->getServiceProvider(),
                isset($request) ? $request : null,
                isset($response) ? $response : null,
                $e
            ));
            $responseString = $e->getMessage();
            if (isset($activeHandler)) {
                $responseString = $activeHandler->getEncoder()->encode(
                    $activeHandler->handleException($e)
                );
            }
            return $responseString;
        }
    }

    /**
     * Handles a CLI route.
     * @param array $args The array of CLI arguments.
     */
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

    /**
     * Performs the standard "handle" step of the routing process.
     * @param string $path The web request path.
     * @param array $query The array of query parameters.
     * @param array $post The array of post parameters.
     * @param string $verb The HTTP verb used in the request.
     * @return Returns the handler to be used for the route.
     */
    protected function determineHandler($path, $query, $post, $verb)
    {
        // determine which handler should handle this path
        $activeHandler = null;
        foreach ($this->getHandlers() as $handler) {
            if (true === $handler->isAppropriate($path, $query, $post, $verb)) {
                $this->invokePluginsHook(
                    'postHandle',
                    array($this->getHandlers(), $handler)
                );
                return $handler;
            }
        }

        throw new Exception\HandlerException('No handler responded to request.');
    }

    /**
     * Performs the standard "handle" step of the routing process but for CLI tasks.
     * @param array $args The array of arguments from the CLI.
     * @return Returns the applicable route handler or null if no handler could be found.
     */
    protected function performCLIHandleStep($args)
    {
        foreach ($this->getHandlers() as $handler) {
            if (false === $handler->isCLIHandler()) {
                continue;
            } elseif (true === $handler->isAppropriate($args)) {
                return $handler;
            }
        }
        return null;
    }

    /**
     * Performs the standard "service" step of the routing process.
     * @param HandlerInterface $activeHandler The current route handler.
     * @return Returns the service expected to perform the route.
     */
    protected function performServiceStep($activeHandler)
    {
        $this->invokePluginsHook('preService', array($activeHandler, $this->getServiceProvider()));

        $request = $activeHandler->getRequest();
        $service = $this->serviceProvider->getServiceInstance($request->getService());
        $method = $request->getMethod();
        if (!method_exists($service, $method)) {
            throw new NoMethodFoundForServiceException(
                'Service '.$request->getService().' does not exist or does not have method '.$method
            );
        }

        // filter the plugins list based on its blacklist/whitelist
        $this->filterPluginsForServiceAndMethod($request->getService(), $method);

        $this->invokePluginsHook(
            'postService',
            array($activeHandler, $this->getServiceProvider(), $request->getService(), $method)
        );

        return $service;
    }

    /**
     * Performs the invocation of the action step.
     * @param HandlerInterface $activeHandler The current route handler.
     * @return Returns the response from the service method.
     */
    protected function performInvokeStep($activeHandler)
    {
        $request = $activeHandler->getRequest();
        $method = $request->getMethod();

        // allow the plugins to invoke pre invoke hooks
        $this->invokePluginsHook(
            'preInvoke',
            array($activeHandler, $this->getServiceProvider(), $request->getService(), $method)
        );

        $service = $this->getServiceProvider()->getServiceInstance($request->getService());
        $response = new RPCResponse($service->$method($request->getPayload()));

        // allow the plugins to invoke post invoke hooks
        $this->invokePluginsHook(
            'postInvoke',
            array($activeHandler, $this->getServiceProvider(), $request->getService(), $method, $response)
        );

        return $response;
    }

    /**
     * Performs the encoding step of the routing process.
     * @param HandlerInterface $activeHandler The current route handler.
     * @param RPCResponse $response The response from the service method.
     * @return Returns the encoded response as a string.
     */
    protected function performEncodeStep($activeHandler, $response)
    {
        $this->invokePluginsHook('preEncode', array($activeHandler, $response));

        $responseString = $activeHandler->getEncoder()->encode($response->getResponseObject());

        $this->invokePluginsHook('postEncode', array($activeHandler, $response, $responseString));

        return $responseString;
    }

    // parses the passed in config file
    private function parseConfig($config)
    {
        $handlers = $this->extractConfigArray($config, self::KEY_HANDLERS);
        $this->setupHandlers($handlers);

        /*
        $plugins = $this->extractConfigArray($config, self::KEY_PLUGINS);
        $this->setupPlugins($plugins);

        $serviceProviderConfig = $this->extractConfigArray($config, self::KEY_SERVICES);
        $this->setupServiceProvider($serviceProviderConfig, $config);

        $this->tasks = isset($config[self::KEY_TASKS]) ? $config[self::KEY_TASKS] : [];
        */
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
        array_walk($handlers, function ($classPath, $handlerClass) {
            $this->handlers[] = $this->retrieveClass(
                $classPath,
                'casino\engine\ServiceRouter\Handler\HandlerInterface',
                'casino\engine\ServiceRouter\Exception\InvalidServiceRouterHandlerException'
            );
        });
    }

    // helps to setup the array of plugins
    private function setupPlugins($plugins)
    {
        $this->plugins = array();
        array_walk($plugins, function ($classPath, $pluginClass) {
            $plugin = $this->retrieveClass(
                $classPath,
                'casino\engine\ServiceRouter\Plugin\RPCPluginInterface',
                'casino\engine\ServiceRouter\Exception\InvalidServiceRouterPluginException'
            );
            if (is_array($classPath)) {
                if (isset($classPath['enable'])) {
                    $plugin->setWhitelist($classPath['enable']);
                } elseif (isset($classPath['disable'])) {
                    $plugin->setBlacklist($classPath['disable']);
                }
            }
            $this->plugins[] = $plugin;
        });
        $this->plugins = $this->sortPlugins($this->plugins);
    }

    private function retrieveClass($value, $interface, $exceptionClass)
    {
        $options = [];
        if (is_string($value)) {
            $className = $value;
        } elseif (is_array($value) && (isset($value['class']) || 1 === count($value))) {
            if (1 === count($value)) {
                $className = array_pop($value);
            } else {
                $className = $value['class'];
                if (isset($value['options'])) {
                    $options = $value['options'];
                }
            }
        } else {
            throw new $exceptionClass(
                'Invalid configuration for helper.'
            );
        }

        if (!class_exists($className)) {
            throw new $exceptionClass('Class '.$className.' does not exist.');
        }

        return new $className($options);
    }

    // helps to configure the service provider
    private function setupServiceProvider($serviceProviderConfig, $config)
    {
        $this->serviceProvider = new Di\ServiceProvider($serviceProviderConfig);
        $this->serviceProvider->setService('config', $config);
    }

    // sorts the list of plugins according to their execution order
    private function sortPlugins($plugins)
    {
        usort($plugins, function ($a, $b) {
            return $a->getExecutionOrder() - $b->getExecutionOrder();
        });
        return $plugins;
    }

    // filters out the list of plugins that may not be compatible with the given
    // service and method based on the plugin's own blacklist/whitelist
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
}
