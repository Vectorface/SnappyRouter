<?php

namespace Vectorface\SnappyRouter\Response;

/**
 * The response to be returned to the client.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class Response extends AbstractResponse
{
    private $responseObject; // the serializable response object
    private $statusCode; // the http response code

    /**
     * Returns the serializable response object.
     * @return mixed The serializable response object.
     */
    public function getResponseObject()
    {
        return $this->responseObject;
    }

    /**
     * Sets the serializable response object.
     * @param mixed $responseObject The serializable response object.
     * @return ResponseInterface Returns $this.
     */
    public function setResponseObject($responseObject)
    {
        $this->responseObject = $responseObject;
        return $this;
    }

    /**
     * Returns the HTTP status code associated with this response.
     * @return integer The HTTP status code associated with this response.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the HTTP status code associated with this response.
     * @param int $statusCode The HTTP status code associated with this response.
     * @return ResponseInterface Returns $this.
     */
    public function setStatusCode($statusCode)
    {
        $statusCode = intval($statusCode);
        $this->statusCode = ($statusCode > 0) ? $statusCode : self::RESPONSE_OK;
        return $this;
    }
}
