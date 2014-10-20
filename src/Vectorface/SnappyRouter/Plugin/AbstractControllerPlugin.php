<?php

namespace Vectorface\SnappyRouter\Plugin;

use Vectorface\SnappyRouter\Controller\AbstractController;
use Vectorface\SnappyRouter\Handler\AbstractHandler;
use Vectorface\SnappyRouter\Request\AbstractRequest;

/**
 * The base class for all controller-based plugins.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractControllerPlugin extends AbstractPlugin implements ControllerPluginInterface
{
    /**
     * Invoked before the handler decides which controller will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param AbstractRequest $request The request to be handled.
     */
    public function beforeControllerSelected(
        AbstractHandler $handler,
        AbstractRequest $request
    ) {

    }

    /**
     * Invoked after the router has decided which controller will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param AbstractRequest $request The request to be handled.
     * @param AbstractController $controller The controller determined to be used.
     * @param string $action The name of the action that will be invoked.
     */
    public function afterControllerSelected(
        AbstractHandler $handler,
        AbstractRequest $request,
        AbstractController $controller,
        $action
    ) {

    }

    /**
     * Invoked before the handler invokes the selected action.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param AbstractRequest $request The request to be handled.
     * @param AbstractController $controller The controller determined to be used.
     * @param string $action The name of the action that will be invoked.
     */
    public function beforeActionInvoked(
        AbstractHandler $handler,
        AbstractRequest $request,
        AbstractController $controller,
        $action
    ) {

    }

    /**
     * Invoked after the handler invoked the selected action.
     * @param AbstractHandler $handler The handler selected by the router.
     * @param AbstractRequest $request The request to be handled.
     * @param AbstractController $controller The controller determined to be used.
     * @param string $action The name of the action that will be invoked.
     * @param mixed $response The response from the controller action.
     */
    public function afterActionInvoked(
        AbstractHandler $handler,
        AbstractRequest $request,
        AbstractController $controller,
        $action,
        $response
    ) {

    }
}
