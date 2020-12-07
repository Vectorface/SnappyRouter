<?php

namespace Vectorface\SnappyRouter\Plugin;

use Exception;
use Vectorface\SnappyRouter\Handler\AbstractHandler;

/**
 * The interface for a SnappyRouter plugin.
 * N.B. It is NOT recommended to implement this interface directly but instead
 * extend the AbstractPlugin class (this provides some actions you almost
 * certainly don't want to write yourself).
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface PluginInterface
{
    /**
     * Invoked directly after the router decides which handler will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     */
    public function afterHandlerSelected(AbstractHandler $handler);

    /**
     * Invoked after the entire route has been handled.
     * @param AbstractHandler $handler The handler selected by the router.
     */
    public function afterFullRouteInvoked(AbstractHandler $handler);

    /**
     * Invoked if an exception is thrown during the route.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param Exception $exception The exception that was thrown.
     */
    public function errorOccurred(AbstractHandler $handler, Exception $exception);

    /**
     * Returns a sortable number for sorting plugins by execution priority. A lower number indicates
     * higher priority.
     * @return integer The execution priority (as a number).
     */
    public function getExecutionOrder();

    /**
     * Sets the controller/action whitelist of this particular plugin. Note that
     * setting a whitelist will remove any previously set blacklists.
     * @param array $whitelist The controller/action whitelist.
     * @return self Returns $this.
     */
    public function setWhitelist($whitelist);

    /**
     * Sets the controller/action blacklist of this particular plugin. Note that
     * setting a blacklist will remove any previously set whitelists.
     * @param array $blacklist The controller/action blacklist.
     * @return self Returns $this.
     */
    public function setBlacklist($blacklist);

    /**
     * Returns whether or not the given controller and action requested should
     * invoke this plugin.
     * @param string $controller The requested controller.
     * @param string $action The requested action.
     * @return boolean Returns true if the given plugin is allowed to run against
     *         this controller/action and false otherwise.
     */
    public function supportsControllerAndAction($controller, $action);
}
