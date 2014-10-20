<?php

namespace Vectorface\SnappyRouter\Plugin;

use \Exception;
use Vectorface\SnappyRouter\Controller\AbstractController;
use Vectorface\SnappyRouter\Handler\AbstractHandler;
use Vectorface\SnappyRouter\Request\AbstractRequest;

/**
 * The base class for all plugins. It is recommended to extend this class
 * instead of implementing the PluginInterface directly.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractPlugin implements PluginInterface
{
    /** the default priority of a plugin */
    const PRIORITY_DEFAULT = 1000;

    /** The plugin options */
    protected $options;

    // properties for plugin/service compatibility
    // both properties cannot be set at the same time (one or the other or both
    // must be null at any point)
    private $whitelist;
    private $blacklist;

    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Invoked directly after the router decides which handler will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     */
    public function afterhandlerSelected(AbstractHandler $handler)
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
     * @return The execution priority (as a number).
     */
    public function getExecutionOrder()
    {
        return self::PRIORITY_DEFAULT;
    }

    /**
     * Sets the controller/action whitelist of this particular plugin. Note that
     * setting a whitelist will remove any previously set blacklists.
     * @param array $whitelist The controller/action whitelist.
     * @return Returns $this.
     */
    public function setWhitelist($whitelist)
    {
        $this->whitelist = $whitelist;
        $this->blacklist = null;
    }

    /**
     * Sets the controller/action blacklist of this particular plugin. Note that
     * setting a blacklist will remove any previously set whitelists.
     * @param array $blacklist The controller/action blacklist.
     * @return Returns $this.
     */
    public function setBlacklist($blacklist)
    {
        $this->whitelist = null;
        $this->blacklist = $blacklist;
    }

    /**
     * Returns whether or not the given controller and action requested should
     * invoke this plugin.
     * @param string $controller The requested controller.
     * @param string $action The requested action.
     * @return bool Returns true if the given plugin is allowed to run against
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
            } else {
                return PluginInterface::KEY_ALL === $this->whitelist[$controller];
            }
        } else {
            // if the controller isn't in the blacklist at all, we're good
            if (!isset($this->blacklist[$controller])) {
                return true;
            }

            // if the controller is in the blacklist then we must check if it is
            // an explicit array of blacklisted actions
            if (is_array($this->blacklist[$controller])) {
                return !in_array($action, $this->blacklist[$controller]);
            } else {
                // if the controller is in the blacklist and isn't an array we
                // assume the whole controller should be blacklisted
                return false;
            }
        }
    }
}
