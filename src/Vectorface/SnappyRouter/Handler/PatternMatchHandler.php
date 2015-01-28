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

    /** the config key for the route cache */
    const KEY_CACHE = 'routeCache';

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

    /** The route information from FastRoute */
    private $routeInfo;

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
        $routeInfo = $this->getRouteInfo($verb, $path);
        if (Dispatcher::FOUND !== $routeInfo[0]) {
            return false;
        }
        $this->callback = $routeInfo[1];
        $this->routeParams = isset($routeInfo[2]) ? $routeInfo[2] : array();
        return true;
    }

    /**
     * Returns the array of route info from the routing library.
     * @param string $verb The HTTP verb used in the request.
     * @param string $path The path to match against the patterns.
     * @param boolean $useCache (optional) An optional flag whether to use the
     *        cached route info or not. Defaults to false.
     * @return array Returns the route info as an array.
     */
    protected function getRouteInfo($verb, $path, $useCache = false)
    {
        if (!$useCache || !isset($this->routeInfo)) {
            $dispatcher = $this->getDispatcher($this->getRoutes());
            $this->routeInfo = $dispatcher->dispatch(strtoupper($verb), $path);
        }
        return $this->routeInfo;
    }

    /**
     * Returns the array of routes.
     * @return array The array of routes.
     */
    protected function getRoutes()
    {
        $options = $this->getOptions();
        return isset($options[self::KEY_ROUTES]) ? $options[self::KEY_ROUTES] : array();
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
     * Returns a request object extracted from the request details (path, query, etc). The method
     * isAppropriate() must have returned true, otherwise this method should return null.
     * @return \Vectorface\SnappyRouter\Request\HttpRequest|null Returns a
     *         Request object or null if this handler is not appropriate.
     */
    public function getRequest()
    {
        return null;
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

        $options = $this->getOptions();
        $cacheData = array();
        if (isset($options[self::KEY_CACHE])) {
            $cacheData = (array)$options[self::KEY_CACHE];
        }

        if (empty($cacheData)) {
            return \FastRoute\simpleDispatcher($f);
        } else {
            return \FastRoute\cachedDispatcher($f, $cacheData);
        }
    }
}
