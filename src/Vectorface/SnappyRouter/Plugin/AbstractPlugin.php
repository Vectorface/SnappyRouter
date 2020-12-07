<?php

namespace Vectorface\SnappyRouter\Plugin;

use Exception;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Di\DiProviderInterface;
use Vectorface\SnappyRouter\Handler\AbstractHandler;

/**
 * The base class for all plugins. It is recommended to extend this class
 * instead of implementing the PluginInterface directly.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractPlugin implements PluginInterface, DiProviderInterface
{
    /** the default priority of a plugin */
    const PRIORITY_DEFAULT = 1000;

    /** A string constant indicating the whitelist/blacklist applies to all
        actions within a controller */
    const ALL_ACTIONS = 'all';

    /** The plugin options */
    protected $options;

    // properties for plugin/service compatibility
    // both properties cannot be set at the same time (one or the other or both
    // must be null at any point)
    private $whitelist;
    private $blacklist;

    /**
     * Constructor for the plugin.
     * @param array $options The array of options.
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Invoked directly after the router decides which handler will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     */
    public function afterHandlerSelected(AbstractHandler $handler)
    {
    }

    /**
     * Invoked after the entire route has been handled.
     * @param AbstractHandler $handler The handler selected by the router.
     */
    public function afterFullRouteInvoked(AbstractHandler $handler)
    {
    }

    /**
     * Invoked if an exception is thrown during the route.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param Exception $exception The exception that was thrown.
     */
    public function errorOccurred(AbstractHandler $handler, Exception $exception)
    {
    }

    /**
     * Returns a sortable number for sorting plugins by execution priority. A lower number indicates
     * higher priority.
     * @return integer The execution priority (as a number).
     */
    public function getExecutionOrder()
    {
        return self::PRIORITY_DEFAULT;
    }

    /**
     * Sets the controller/action whitelist of this particular plugin. Note that
     * setting a whitelist will remove any previously set blacklists.
     * @param array $whitelist The controller/action whitelist.
     * @return self Returns $this.
     */
    public function setWhitelist($whitelist)
    {
        $this->whitelist = $whitelist;
        $this->blacklist = null;
        return $this;
    }

    /**
     * Sets the controller/action blacklist of this particular plugin. Note that
     * setting a blacklist will remove any previously set whitelists.
     * @param array $blacklist The controller/action blacklist.
     * @return self Returns $this.
     */
    public function setBlacklist($blacklist)
    {
        $this->whitelist = null;
        $this->blacklist = $blacklist;
        return $this;
    }

    /**
     * Returns whether or not the given controller and action requested should
     * invoke this plugin.
     * @param string $controller The requested controller.
     * @param string $action The requested action.
     * @return boolean Returns true if the given plugin is allowed to run against
     *         this controller/action and false otherwise.
     */
    public function supportsControllerAndAction($controller, $action)
    {
        if (null === $this->blacklist) {
            if (null === $this->whitelist) {
                // plugin has global scope
                return true;
            }
            // we use a whitelist so ensure the controller is in the whitelist
            if (!isset($this->whitelist[$controller])) {
                return false;
            }
            // the whitelisted controller could be an array of actions or it could
            // be mapped to the "all" string
            if (is_array($this->whitelist[$controller])) {
                return in_array($action, $this->whitelist[$controller]);
            }
            return self::ALL_ACTIONS === (string)$this->whitelist[$controller];
        }

        // if the controller isn't in the blacklist at all, we're good
        if (!isset($this->blacklist[$controller])) {
            return true;
        }

        // if the controller is not an array we return false
        // otherwise we check if the action is listed in the array
        return is_array($this->blacklist[$controller]) &&
              !in_array($action, $this->blacklist[$controller]);
    }

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
}
