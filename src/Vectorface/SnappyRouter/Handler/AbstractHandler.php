<?php

namespace Vectorface\SnappyRouter\Handler;

use \Exception;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Di\DiProviderInterface;
use Vectorface\SnappyRouter\Di\ServiceProvider;
use Vectorface\SnappyRouter\Encoder\NullEncoder;
use Vectorface\SnappyRouter\Exception\PluginException;

/**
 * The base class for all handlers.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractHandler implements DiProviderInterface
{
    const KEY_CLASS    = 'class';
    const KEY_OPTIONS  = 'options';
    const KEY_SERVICES = 'services';
    const KEY_PLUGINS  = 'plugins';

    /** an array of handler-specific options */
    protected $options;

    /** a sorted array of handler plugins */
    private $plugins;

    /** the service provider to use */
    private $serviceProvider;

    /**
     * Constructor for the class.
     * @param array $options An array of options for the plugin.
     */
    public function __construct($options)
    {
        $this->options = $options;
        $this->plugins = array();
        if (isset($options[self::KEY_PLUGINS])) {
            $this->setPlugins((array)$options[self::KEY_PLUGINS]);
        }
        $services = array();
        if (isset($options[self::KEY_SERVICES])) {
            $services = (array)$options[self::KEY_SERVICES];
        }
        $this->serviceProvider = new ServiceProvider($services);
    }

    /**
     * Retrieve an element from the DI container.
     * @param string $key The DI key.
     * @param boolean $useCache (optional) An optional indicating whether we
     *        should use the cached version of the element (true by default).
     * @return Returns the DI element mapped to that key.
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
     * @param array $plugins The array of plugins.
     * @return AbstractHandler Returns $this.
     */
    public function setPlugins($plugins)
    {
        $this->plugins = array();
        foreach ($plugins as $key => $plugin) {
            $pluginClass = $plugin;
            if (is_array($plugin)) {
                if (!isset($plugin[AbstractHandler::KEY_CLASS])) {
                    throw new PluginException('Invalid or missing class for plugin '.$key);
                } elseif (!class_exists($plugin[AbstractHandler::KEY_CLASS])) {
                    throw new PluginException('Invalid or missing class for plugin '.$key);
                }
                $pluginClass = $plugin[AbstractHandler::KEY_CLASS];
            }
            $options = array();
            if (isset($plugin[AbstractHandler::KEY_OPTIONS])) {
                $options = (array($plugin[AbstractHandler::KEY_OPTIONS]));
            }
            $this->plugins[] = new $pluginClass($options);
        }
        $this->plugins = $this->sortPlugins($this->plugins);
        return $this;
    }

    // sorts the list of plugins according to their execution order
    private function sortPlugins($plugins)
    {
        usort($plugins, function ($a, $b) {
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
                call_user_func_array(array($plugin, $hook), $args);
            }
        }
    }

    /**
     * Performs the actual routing.
     * @return Returns the result of the route.
     */
    abstract public function performRoute();

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
     * @return Returns a serializable value that will be encoded and returned
     *         to the client.
     */
    public function handleException(Exception $e)
    {
        return $e->getMessage();
    }
}
