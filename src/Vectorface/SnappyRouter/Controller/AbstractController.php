<?php

namespace Vectorface\SnappyRouter\Controller;

use \Exception;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Di\DiProviderInterface;
use Vectorface\SnappyRouter\Handler\AbstractRequestHandler;
use Vectorface\SnappyRouter\Request\HttpRequest;

/**
 * An abstract base controller that should be extended by all other controllers.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractController implements DiProviderInterface
{
    /** The web request being made. */
    private $request;

    /** The array of view context variables. */
    protected $viewContext;

    /** The handler being used by the router. */
    protected $handler;

    /**
     * This method is called before invoking any specific controller action.
     * Override this method to provide your own logic for the subclass but
     * ensure you make a call to parent::initialize() as well.
     * @param HttpRequest $request The web request being made.
     * @param AbstractRequestHandler $handler The handler the router is using.
     * @return AbstractController Returns $this.
     */
    public function initialize(HttpRequest $request, AbstractRequestHandler $handler)
    {
        $this->request = $request;
        $this->handler = $handler;
        $this->viewContext = array();
        return $this;
    }

    /**
     * Renders the view for the given controller and action.
     * @param array $viewVariables An array of additional parameters to add
     *        to the existing view context.
     * @param string $template The name of the view template.
     * @return Returns the rendered view as a string.
     */
    public function renderView($viewVariables, $template)
    {
        $encoder = $this->handler->getEncoder();
        if (method_exists($encoder, 'renderView')) {
            return $encoder->renderView(
                $template,
                array_merge($this->viewContext, (array)$viewVariables)
            );
        } else {
            throw new Exception('The current encoder does not support the render view method.');
        }
    }

    /**
     * Returns the request object.
     * @return HttpRequest The request object.
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the view context.
     * @return array The view context.
     */
    public function getViewContext()
    {
        return $this->viewContext;
    }

    /**
     * Retrieve an element from the DI container.
     * @param string $key The DI key.
     * @param boolean $useCache (optional) An optional indicating whether we
     *        should use the cached version of the element (true by default).
     * @return mixed Returns the DI element mapped to that key.
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
