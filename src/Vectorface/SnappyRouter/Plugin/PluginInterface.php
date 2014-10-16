<?php

namespace Vectorface\SnappyRouter\Plugin;

/**
 * The interface for a SnappyRouter plugin.
 * N.B. It is NOT recommended to implement this interface directly but instead
 * extend the AbstractPlugin class (this provides some methods you almost
 * certainly don't want to write yourself).
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface PluginInterface
{
    /** A string constant indicating the whitelist/blacklist applies to all
        methods within a service */
    const METHOD_KEY_ALL = 'all';

    /**
     * Invoked directly after the router decides which handler will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     */
    public function afterhandlerSelected($handler);

    /**
     * Invoked before the handler decides which controller will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param AbstractRequest $request The request to be handled.
     */
    public function beforeControllerSelected($handler, $request);

    /**
     * Invoked after the router has decided which controller will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param AbstractRequest $request The request to be handled.
     * @param AbstractController $controller The controller determined to be used.
     * @param string $action The name of the action that will be invoked.
     */
    public function afterControllerSelected($handler, $request, $controller, $action);

    /**
     * Invoked before the handler invokes the selected action.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param AbstractRequest $request The request to be handled.
     * @param AbstractController $controller The controller determined to be used.
     * @param string $action The name of the action that will be invoked.
     */
    public function beforeActionInvoked($handler, $request, $controller, $action);

    /**
     * Invoked after the handler invoked the selected action.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param AbstractRequest $request The request to be handled.
     * @param AbstractController $controller The controller determined to be used.
     * @param string $action The name of the action that will be invoked.
     * @param mixed $response The response from the controller action.
     */
    public function afterActionInvoked($handler, $request, $controller, $action, $response);

    /**
     * Invoked if an exception is thrown during the route.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param Exception $exception The exception that was thrown.
     */
    public function errorOccurred($handler, Exception $exception);

    /**
     * Returns a sortable number for sorting plugins by execution priority. A lower number indicates
     * higher priority.
     * @return The execution priority (as a number).
     */
    public function getExecutionOrder();

    /**
     * Sets the service/method whitelist of this particular plugin. Note that
     * setting a whitelist will remove any previously set blacklists.
     * @param array $whitelist The service/method whitelist.
     * @return Returns $this.
     */
    public function setWhitelist($whitelist);

    /**
     * Sets the service/method blacklist of this particular plugin. Note that
     * setting a blacklist will remove any previously set whitelists.
     * @param array $blacklist The service/method blacklist.
     * @return Returns $this.
     */
    public function setBlacklist($blacklist);

    /**
     * Returns whether or not the given service and method requested should
     * invoke this plugin.
     * @param string $service The requested service.
     * @param string $method The requested method.
     * @return Returns true if the given plugin is allowed to run against this
     *         service/method and false otherwise.
     */
    public function supportsServiceAndMethod($service, $method);
}
