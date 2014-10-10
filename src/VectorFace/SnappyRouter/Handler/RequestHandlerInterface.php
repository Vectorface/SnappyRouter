<?php

namespace VectorFace\SnappyRouter\Handler;

use \Exception;

/**
 * The interface for all handlers that implement a request/response style of
 * invokation.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface RequestHandlerInterface
{
    /**
     * Returns true if the handler determines it should handle this request and false otherwise.
     * @param string $path The URL path for the request.
     * @param array $query The query parameters.
     * @param array $post The post data.
     * @param string $verb The HTTP verb used in the request.
     * @return Returns true if this handler will handle the request and false otherwise.
     */
    public function isAppropriate($path, $query, $post, $verb);

    /**
     * Provides the handler with an opportunity to perform any last minute
     * error handling logic. The returned value will be serialized by the 
     * handler's encoder.
     * @param Exception $e The exception that was thrown.
     * @return Returns a serializable value that will be encoded and returned
     *         to the client.
     */
    public function handleException(Exception $e);
}
