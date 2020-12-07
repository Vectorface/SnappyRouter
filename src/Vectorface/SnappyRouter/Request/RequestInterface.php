<?php

namespace Vectorface\SnappyRouter\Request;

use Vectorface\SnappyRouter\Controller\AbstractController;

/**
 * The standard interface for requests.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface RequestInterface
{
    /**
     * Returns the controller to be used in the request.
     * @return string Returns the controller DI key to be used in the request.
     */
    public function getController();

    /**
     * Sets the controller to be used in the request.
     * @param AbstractController $controller The controller to be used in the request.
     * @return RequestInterface Returns $this.
     */
    public function setController($controller);

    /**
     * Returns the action to be invoked as a string.
     * @return string The action to be invoked.
     */
    public function getAction();

    /**
     * Sets the action to be invoked by the request
     * @param string $action The action to be invoked by the request.
     * @return RequestInterface Returns $this.
     */
    public function setAction($action);
}
