<?php

namespace Vectorface\SnappyRouter\Exception;

use Exception;
use Vectorface\SnappyRouter\Response\AbstractResponse;

/**
 * An exception indicating an invalid HTTP action was specified.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class MethodNotAllowedException extends Exception implements RouterExceptionInterface
{
    // as per RFC2616 (http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.6)
    // we must specify a comma separated list of allowed methods if we return
    // a 405 response
    private $allowedMethods;

    /**
     * Constructor for the method.
     * @param string $message The error message string.
     * @param array $allowedMethods The array of methods that are allowed.
     */
    public function __construct($message, $allowedMethods)
    {
        parent::__construct($message);
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * Returns the associated status code with the exception. By default, most exceptions correspond
     * to a server error (HTTP 500). Override this method if you want your exception to generate a
     * different status code.
     * @return int The associated status code.
     */
    public function getAssociatedStatusCode()
    {
        if (!empty($this->allowedMethods) && is_array($this->allowedMethods)) {
            @header(sprintf('Allow: %s', implode(',', $this->allowedMethods)));
        }
        return AbstractResponse::RESPONSE_METHOD_NOT_ALLOWED;
    }
}
