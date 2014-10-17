<?php

namespace Vectorface\SnappyRouter\Controller;

use Vectorface\SnappyRouter\Request\HttpRequest;

/**
 * An abstract base controller that should be extended by all other controllers.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractController
{
    /** The web request being made. */
    protected $request;

    /** The view rendering environment. */
    protected $viewEnvironment;

    /** The array of view context variables. */
    protected $viewContext;

    /**
     * This method is called before invoking any specific controller action.
     * Override this method to provide your own logic for the subclass but
     * ensure you make a call to parent::initialize() as well.
     * @param HttpRequest $request The web request being made.
     * @return AbstractController Returns $this.
     */
    public function initialize(HttpRequest $request)
    {
        /*
        $this->serviceProvider = $serviceProvider;
        $this->request = $request;

        $config = $this->getServiceProvider()->getService('config');
        require_once __DIR__.'/Twig/Autoloader.php';
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem($config['views']['path']);
        $this->viewEnvironment = new \Twig_Environment($loader, $config['views']);
        $this->viewEnvironment->addFilter(
            new \Twig_SimpleFilter(
                'intval',
                function ($string) {
                    return intval($string);
                }
            )
        );

        // setup the prod/beta environment
        $isProd = \Config::isProd();
        $this->viewContext['environmentSpan'] = sprintf(
            '<span style="color:%s">%s</span>',
            $isProd ? 'red' : 'green',
            $isProd ? 'Prod' : 'Beta'
        );

        $this->viewContext['productionEnvironment'] = $isProd;
        $this->viewContext['baseUri'] = $config['basePath'];
        $this->viewContext['pageTitle'] = 'Engine Management and Setup';
        $this->viewContext['assets'] = $config['assets'];
        */
    }

    /**
     * Renders the view for the given controller and action.
     * @param string $controller The controller to render.
     * @param string $action The action to render.
     * @param array $params (optional) An array of additional parameters to add
     *        to the existing view context.
     * @return Returns the rendered view as a string.
     */
    /*
    public function renderView($controller, $action, $params = [])
    {
        $controller = strtolower(substr($controller, 0, strrpos($controller, 'Controller')));
        $action     = strtolower(substr($action, 0, strrpos($action, 'Action')));
        $viewPath = sprintf('%s/%s.twig', $controller, $action);
        $template = $this->viewEnvironment->loadTemplate($viewPath);
        return $template->render(array_merge($this->viewContext, $params));
    }
    */
}
