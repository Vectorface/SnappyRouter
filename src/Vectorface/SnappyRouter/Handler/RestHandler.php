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
    const ROUTE_PATTERN_VERSION = 'v{version}';

    /** object ID version pattern */
    const ROUTE_PATTERN_OBJECT_ID = '{objectId:[0-9]+}';

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
        return true;
    }

    /**
     * Returns the active response encoder.
     * @return EncoderInterface Returns the response encoder.
     */
    public function getEncoder()
    {
        return new JsonEncoder();
    }

    /**
     * Returns the array of routes.
     * @return array The array of routes.
     */
    protected function getRoutes()
    {
        $c = parent::ROUTE_PATTERN_CONTROLLER;
        $a = parent::ROUTE_PATTERN_ACTION;
        $v = self::ROUTE_PATTERN_VERSION;
        $o = self::ROUTE_PATTERN_OBJECT_ID;
        return array(
            "/$v/$c" => self::MATCHES_CONTROLLER,
            "/$v/$c/" => self::MATCHES_CONTROLLER,
            "/$v/$c/$a" => self::MATCHES_CONTROLLER_AND_ACTION,
            "/$v/$c/$a/" => self::MATCHES_CONTROLLER_AND_ACTION,
            "/$v/$c/$o" => self::MATCHES_CONTROLLER_AND_ID,
            "/$v/$c/$o/" => self::MATCHES_CONTROLLER_AND_ID,
            "/$v/$c/$a/$o" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v/$c/$a/$o/" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v/$c/$o/$a" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
            "/$v/$c/$o/$a/" => self::MATCHES_CONTROLLER_ACTION_AND_ID,
        );
    }
}
