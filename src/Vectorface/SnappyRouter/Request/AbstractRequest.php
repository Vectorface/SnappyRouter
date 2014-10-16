<?php

namespace Vectorface\SnappyRouter\Request;

use Vectorface\SnappyRouter\Controller\AbstractController;

class AbstractRequest implements RequestInterface
{
    /** The controller to use in the request. */
    private $controller;
    /** The action to invoke in the request. */
    private $action;

    /**
     * Constructor for the abstract request.
     * @param string $controller The controller to be used.
     * @param string $action The action to be invoked.
     * @param string $verb The HTTP verb used in the request.
     */
    public function __construct($controller, $action)
    {
        $this->setController($controller);
        $this->setAction($action);
    }

    /**
     * Returns the controller to be used in the request.
     * @return AbstractController Returns the controller to be used in the request.
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Sets the controller to be used in the request.
     * @param AbstractController $controller The controller to be used in the request.
     * @return RequestInterface Returns $this.
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Returns the action to be invoked as a string.
     * @return string The action to be invoked.
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Sets the action to be invoked by the request
     * @param string $action The action to be invoked by the request.
     * @return RequestInterface Returns $this.
     */
    public function setAction($action)
    {
        $this->action = (string)$action;
        return $this;
    }
}
