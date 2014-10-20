<?php

namespace Vectorface\SnappyRouter\Response;

/**
 * An abstract class that defines a basic constructor for the response as well
 * as the required methods the implementation requires.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractResponse implements ResponseInterface
{
    /** HTTP response code for OK */
    const RESPONSE_OK = 200;
    /** HTTP response code for a bad request */
    const RESPONSE_BAD_REQUEST = 400;
    /** HTTP response code for unauthorized */
    const RESPONSE_UNAUTHORIZED = 401;
    /** HTTP response code for forbidden */
    const RESPONSE_FORBIDDEN = 403;
    /** HTTP response code for not found */
    const RESPONSE_NOT_FOUND = 404;
    /** HTTP response code for method not allowed */
    const RESPONSE_METHOD_NOT_ALLOWED = 405;
    /** HTTP response code for too many requests in a period of time
     *  See: http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#429 */
    const RESPONSE_RATE_LIMITED = 429;
    /** HTTP response code for a server error */
    const RESPONSE_SERVER_ERROR = 500;
    /** HTTP response code for server unavailable */
    const RESPONSE_SERVICE_UNAVAILABLE = 503;

    /**
     * Constructor for the response.
     * @param mixed $responseObject A response object that can be serialized to a string.
     * @param int $statusCode The HTTP response.
     */
    public function __construct($responseObject, $statusCode = self::RESPONSE_OK)
    {
        $this->setResponseObject($responseObject);
        $this->setStatusCode($statusCode);
    }
}
