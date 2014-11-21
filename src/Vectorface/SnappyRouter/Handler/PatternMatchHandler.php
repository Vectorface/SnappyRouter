<?php

namespace Vectorface\SnappyRouter\Handler;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

/**
 * A handler for matching route patterns and mapping to a method.
 * Internally the class uses FastRoute to do the pattern matching.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class PatternMatchHandler extends AbstractRequestHandler
{
    /** the config key for the list of routes */
    const KEY_ROUTES = 'routes';

    // the currently active callback
    private $callback;
    // the currently active route parameters
    private $routeParams;

    /** All supported HTTP verbs */
    private static $allHttpVerbs = array(
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'OPTIONS'
    );

    /**
     * Returns true if the handler determines it should handle this request and false otherwise.
     * @param string $path The URL path for the request.
     * @param array $query The query parameters.
     * @param array $post The post data.
     * @param string $verb The HTTP verb used in the request.
     * @return boolean Returns true if this handler will handle the request and false otherwise.
     */
    public function isAppropriate($path, $query, $post, $verb)
    {
        $options = $this->getOptions();
        $dispatcher = $this->getDispatcher($options['routes']);
        $routeInfo = $dispatcher->dispatch(strtoupper($verb), $path);
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                break;
            default:
                return false;
        }
        $this->callback = $routeInfo[1];
        $this->routeParams = isset($routeInfo[2]) ? $routeInfo[2] : array();
        return true;
    }

    /**
     * Performs the actual routing.
     * @return mixed Returns the result of the route.
     */
    public function performRoute()
    {
        return call_user_func($this->callback, $this->routeParams);
    }

    /**
     * Returns an instance of the FastRoute dispatcher.
     * @param array $routes The array of specified routes.
     * @return FastRoute\Dispatcher The dispatcher to use.
     */
    private function getDispatcher($routes)
    {
        $verbs = self::$allHttpVerbs;
        $f = function (RouteCollector $collector) use ($routes, $verbs) {
            foreach ($routes as $pattern => $route) {
                if (is_array($route)) {
                    foreach ($route as $verb => $callback) {
                        $collector->addRoute(strtoupper($verb), $pattern, $callback);
                    }
                } else {
                    foreach ($verbs as $verb) {
                        $collector->addRoute($verb, $pattern, $route);
                    }
                }
            }
        };
        return \FastRoute\simpleDispatcher($f);
    }
}
