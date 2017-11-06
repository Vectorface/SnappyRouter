<?php

namespace Vectorface\SnappyRouter\Handler;

use Vectorface\SnappyRouter\Encoder\JsonEncoder;

/**
 * Handles REST-like URLs like '/api/v2/users/1/details'.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class RestHandler extends ControllerHandler
{
    /** Constants indicating the type of route */
    const MATCHES_ID = 8;
    const MATCHES_CONTROLLER_AND_ID = 9;
    const MATCHES_CONTROLLER_ACTION_AND_ID = 11;

    /** API version pattern */
    const ROUTE_PATTERN_VERSION_ONE   = 'v{version:\d+}';
    const ROUTE_PATTERN_VERSION_TWO   = 'v{version:\d+\.\d+}';
    const ROUTE_PATTERN_VERSION_THREE = 'v{version:\d+\.\d+\.\d+}';

    /** object ID version pattern */
    const ROUTE_PATTERN_OBJECT_ID = '{objectId:\d+}';

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
        // use the parent method to match the routes
        if (false === parent::isAppropriate($path, $query, $post, $verb)) {
            return false;
        }

        // determine the route information from the path
        $routeInfo = $this->getRouteInfo($verb, $path, true);
        $this->routeParams = array($routeInfo[2]['version']);
        if ($routeInfo[1] & self::MATCHES_ID) {
            $this->routeParams[] = intval($routeInfo[2]['objectId']);
        }

        // use JSON encoder by default
        $this->encoder = new JsonEncoder();

        return true;
    }

    /**
     * Returns the array of routes.
     * @return array The array of routes.
     */
    protected function getRoutes()
    {
        $c = parent::ROUTE_PATTERN_CONTROLLER;
        $a = parent::ROUTE_PATTERN_ACTION;
        $v1 = self::ROUTE_PATTERN_VERSION_ONE;
        $v2 = self::ROUTE_PATTERN_VERSION_TWO;
        $v3 = self::ROUTE_PATTERN_VERSION_THREE;
        $o = self::ROUTE_PATTERN_OBJECT_ID;
        return array(
            "/$v1/$c" => self::MATCHES_CONTROLLER,
            "/$v1/$c/" => self::MATCHES_CONTROLLER,
            "/$v2/$c" => self::MATCHES_CONTROLLER,
            "/$v2/$c/" => self::MATCHES_CONTROLLER,
            "/$v3/$c" => self::MATCHES_CONTROLLER,
            "/$v3/$c/" => self::MATCHES_CONTROLLER,
            "/$v1/$c/$a" => self::MATCHES_CONTROLLER_AND_ACTION,
            "/$v1/$c/$a/" => self::MATCHES_CONTROLLER_AND_ACTION,
            "/$v2/$c/$a" => self::MATCHES_CONTROLLER_AND_ACTION,
            "/$v2/$c/$a/" => self::MATCHES_CONTROLLER_AND_ACTION,
            "/$v3/$c/$a" => self::MATCHES_CONTROLLER_AND_ACTION,
            "/$v3/$c/$a/" => self::MATCHES_CONTROLLER_AND_ACTION,
            "/$v1/$c/$o" => self::MATCHES_CONTROLLER_AND_ID,
            "/$v1/$c/$o/" => self::MATCHES_CONTROLLER_AND_ID,
            "/$v2/$c/$o" => self::MATCHES_CONTROLLER_AND_ID,
            "/$v2/$c/$o/" => self::MATCHES_CONTROLLER_AND_ID,
            "/$v3/$c/$o" => self::MATCHES_CONTROLLER_AND_ID,
            "/$v3/$c/$o/" => self::MATCHES_CONTROLLER_AND_ID,
            "/$v1/$c/$a/$o" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v1/$c/$a/$o/" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v1/$c/$o/$a" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v1/$c/$o/$a/" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v2/$c/$a/$o" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v2/$c/$a/$o/" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v2/$c/$o/$a" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v2/$c/$o/$a/" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v3/$c/$a/$o" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v3/$c/$a/$o/" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v3/$c/$o/$a" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v3/$c/$o/$a/" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
        );
    }
}
