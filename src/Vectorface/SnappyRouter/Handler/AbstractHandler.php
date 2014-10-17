<?php

namespace Vectorface\SnappyRouter\Handler;

use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Di\DiProvider;
use Vectorface\SnappyRouter\Di\ServiceProvider;

/**
 * The base class for all handlers.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractHandler implements DiProvider
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
            $this->plugins = $this->sortPlugins((array)$options[self::KEY_PLUGINS]);
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
     * Performs the actual routing.
     * @return Returns the result of the route.
     */
    abstract public function performRoute();

    /**
     * Returns the array of plugins registered with this handler.
     */
    public function getPlugins()
    {
        return $this->plugins;
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
     */
    public function invokePluginsHook($hook, $args)
    {
        foreach ($this->getPlugins() as $plugin) {
            call_user_func_array(array($plugin, $hook), $args);
        }
    }
}
