<?php

namespace Vectorface\SnappyRouter\Controller;

use \Exception;
use Vectorface\SnappyRouter\Di\Di;
use Vectorface\SnappyRouter\Di\DiProviderInterface;
use Vectorface\SnappyRouter\Handler\ControllerHandler;
use Vectorface\SnappyRouter\Request\HttpRequest;

/**
 * An abstract base controller that should be extended by all other controllers.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractController implements DiProviderInterface
{
    const KEY_VIEW_ENVIRONMENT = 'viewEnvironment';

    /** The web request being made. */
    private $request;

    /** The array of view context variables. */
    protected $viewContext;

    /** The default view to render if no view is specified */
    private $defaultView;

    /**
     * This method is called before invoking any specific controller action.
     * Override this method to provide your own logic for the subclass but
     * ensure you make a call to parent::initialize() as well.
     * @param HttpRequest $request The web request being made.
     * @param string $defaultView The default view to render.
     * @return AbstractController Returns $this.
     */
    public function initialize(HttpRequest $request, $defaultView)
    {
        $this->request = $request;
        $this->defaultView = $defaultView;
        $this->viewContext = array();

        // initialize the view environment
        $viewConfig = $this->get(ControllerHandler::KEY_VIEWS);
        if (!empty($viewConfig)) {
            $loader = new \Twig_Loader_Filesystem($viewConfig[ControllerHandler::KEY_VIEWS_PATH]);
            $this->set(self::KEY_VIEW_ENVIRONMENT, new \Twig_Environment($loader, $viewConfig));
        }
        return $this;
    }

    /**
     * Renders the view for the given controller and action.
     * @param string $controller The controller to render.
     * @param string $action The action to render.
     * @param array $params (optional) An array of additional parameters to add
     *        to the existing view context.
     * @return Returns the rendered view as a string.
     */
    public function renderView($viewVariables, $view = null)
    {
        if (null === $view) {
            $view = $this->defaultView;
        }
        $template = $this->get(self::KEY_VIEW_ENVIRONMENT)->loadTemplate($view);
        return $template->render(array_merge($this->viewContext, $viewVariables));
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
}
