<?php

namespace VectorFace\SnappyRouter\Plugin;

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
     * Invoked before the service router decides which handler will be used.
     * @param array $handlers The array of possible handlers.
     * @param string $path The URL path for the request.
     * @param array $query The query parameters.
     * @param array $post The post data.
     * @param string $verb The HTTP verb used in the request.
     */
    public function preHandle(&$handlers, &$path, &$query, &$post, &$verb);

    /**
     * Invoked after the service router has decided which handler will be used.
     * @param array $handlers The array of possible handlers.
     * @param HandlerInterface $handler The handler selected by the service router.
     */
    public function postHandle(&$handlers, $handler);

    /**
     * Invoked before the service router decides which service will be used.
     * @param HandlerInterface $handler The handler selected by the service router.
     * @param ServiceProvider $serviceProvider The service provider used by the router.
     */
    public function preService($handler, $serviceProvider);

    /**
     * Invoked after the service router has decided which service will be used.
     * @param HandlerInterface $handler The handler selected by the service router.
     * @param ServiceProvider $serviceProvider The service provider used by the router.
     * @param string $service The service selected to provide the response.
     * @param string $method The method that will be invoked.
     */
    public function postService($handler, $serviceProvider, &$service, &$method);

    /**
     * Invoked before the service router invokes the actual service method.
     * @param HandlerInterface $handler The handler selected by the service router.
     * @param ServiceProvider $serviceProvider The service provider used by the router.
     * @param string $service The service that will be used to provide the response.
     * @param string $method The method that will be invoked.
     */
    public function preInvoke($handler, $serviceProvider, &$service, &$method);

    /**
     * Invoked after the service router has invoked the service method.
     * @param HandlerInterface $handler The handler selected by the service router.
     * @param ServiceProvider $serviceProvider The service provider used by the router.
     * @param string $service The service that will be used to provide the response.
     * @param string $method The method that will be invoked.
     * @param RPCResponse $response The response object that will be going out.
     */
    public function postInvoke($handler, $serviceProvider, &$service, &$method, $response);

    /**
     * Invoked after the service router has invoked the service method.
     * @param HandlerInterface $handler The handler selected by the service router.
     * @param RPCResponse $response The response object that will be going out.
     */
    public function preEncode($handler, $response);

    /**
     * Invoked after the service router has invoked the service method.
     * @param HandlerInterface $handler The handler selected by the service router.
     * @param RPCResponse $response The response object that will be going out.
     * @param string $responseString The response object as an encoded string.
     */
    public function postEncode($handler, $response, &$responseString);

    /**
     * Invoked if an exception is thrown during the routing phase.
     * @param HandlerInterface $handler The handler selected by the service router. This could be
     * null if the error occurred before the handler was selected.
     * @param ServiceProvider $serviceProvider The service provider used by the router.
     * @param RPCRequst $request The request being made. This could be null if the error occurred
     * before the request was determined.
     * @param RPCResponse $response The response being sent back. This could be null if the error
     * @param Exception $exception The exception that was thrown.
     * occurred before the response was determined.
     */
    public function onError($handler, $serviceProvider, $request, $response, &$exception);

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
