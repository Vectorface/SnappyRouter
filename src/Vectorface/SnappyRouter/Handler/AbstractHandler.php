<?php

namespace Vectorface\SnappyRouter\Handler;

use Exception;
use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Di\DiProviderInterface;
use Vectorface\SnappyRouter\Di\ServiceProvider;
use Vectorface\SnappyRouter\Encoder\EncoderInterface;
use Vectorface\SnappyRouter\Encoder\NullEncoder;
use Vectorface\SnappyRouter\Exception\PluginException;

/**
 * The base class for all handlers.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractHandler implements DiProviderInterface
{
    /** An array of handler-specific options */
    protected $options;

    /** A sorted array of handler plugins */
    private $plugins;

    /** The service provider to use */
    private $serviceProvider;

    /**
     * Constructor for the class.
     *
     * @param array $options An array of options for the plugin.
     * @throws PluginException
     */
    public function __construct($options)
    {
        $this->options = $options;
        $this->plugins = [];
        if (isset($options[Config::KEY_PLUGINS])) {
            $this->setPlugins((array)$options[Config::KEY_PLUGINS]);
        }
        // configure the service provider
        $services = [];
        if (isset($options[Config::KEY_CONTROLLERS])) {
            $services = (array)$options[Config::KEY_CONTROLLERS];
        }
        $this->serviceProvider = new ServiceProvider($services);
        if (isset($options[Config::KEY_NAMESPACES])) {
            // namespace provisioning
            $this->serviceProvider->setNamespaces((array)$options[Config::KEY_NAMESPACES]);
        } elseif (isset($options[Config::KEY_FOLDERS])) {
            // folder provisioning
            $this->serviceProvider->setFolders((array)$options[Config::KEY_FOLDERS]);
        }
    }

    /**
     * Performs the actual routing.
     * @return mixed Returns the result of the route.
     */
    abstract public function performRoute();

    /**
     * Retrieve an element from the DI container.
     *
     * @param string $key The DI key.
     * @param boolean $useCache (optional) An optional indicating whether we
     *        should use the cached version of the element (true by default).
     * @return mixed Returns the DI element mapped to that key.
     * @throws Exception
     */
    public function get($key, $useCache = true)
    {
        return Di::getDefault()->get($key, $useCache);
    }

    /**
     * Sets an element in the DI container for the specified key.
     * @param string $key The DI key.
     * @param mixed  $element The DI element to store.
     * @return Di Returns the Di instance.
     */
    public function set($key, $element)
    {
        return Di::getDefault()->set($key, $element);
    }

    /**
     * Returns the active service provider for this handler.
     * @return ServiceProvider The active service provider for this handler.
     */
    public function getServiceProvider()
    {
        return $this->serviceProvider;
    }

    /**
     * Returns the array of plugins registered with this handler.
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * Sets the current list of plugins.
     *
     * @param array $plugins The array of plugins.
     * @return AbstractHandler Returns $this.
     * @throws PluginException
     */
    public function setPlugins($plugins)
    {
        $this->plugins = [];
        foreach ($plugins as $key => $plugin) {
            $pluginClass = $plugin;
            if (is_array($plugin)) {
                if (!isset($plugin[Config::KEY_CLASS])) {
                    throw new PluginException('Invalid or missing class for plugin '.$key);
                } elseif (!class_exists($plugin[Config::KEY_CLASS])) {
                    throw new PluginException('Invalid or missing class for plugin '.$key);
                }
                $pluginClass = $plugin[Config::KEY_CLASS];
            }
            $options = [];
            if (isset($plugin[Config::KEY_OPTIONS])) {
                $options = (array)$plugin[Config::KEY_OPTIONS];
            }
            $this->plugins[] = new $pluginClass($options);
        }
        $this->plugins = $this->sortPlugins($this->plugins);
        return $this;
    }

    /**
     * Sorts the list of plugins according to their execution order
     *
     * @param array $plugins
     * @return array
     */
    private function sortPlugins($plugins)
    {
        usort($plugins, function($a, $b) {
            return $a->getExecutionOrder() - $b->getExecutionOrder();
        });
        return $plugins;
    }

    /**
     * Invokes the plugin hook against all the listed plugins.
     * @param string $hook The hook to invoke.
     * @param array $args The arguments to pass to the call.
     */
    public function invokePluginsHook($hook, $args)
    {
        foreach ($this->getPlugins() as $plugin) {
            if (method_exists($plugin, $hook)) {
                call_user_func_array([$plugin, $hook], $args);
            }
        }
    }

    /**
     * Returns whether a handler should function in a CLI environment.
     * @return bool Returns true if the handler should function in a CLI
     *         environment and false otherwise.
     */
    abstract public function isCliHandler();

    /**
     * Returns the active response encoder.
     * @return EncoderInterface Returns the response encoder.
     */
    public function getEncoder()
    {
        return new NullEncoder();
    }

    /**
     * Provides the handler with an opportunity to perform any last minute
     * error handling logic. The returned value will be serialized by the
     * handler's encoder.
     * @param Exception $e The exception that was thrown.
     * @return string Returns a serializable value that will be encoded and returned to the client.
     */
    public function handleException(Exception $e)
    {
        return $e->getMessage();
    }

    /**
     * Returns the array of options.
     * @return array $options The array of options.
     */
    public function getOptions()
    {
        return $this->options;
    }
}
