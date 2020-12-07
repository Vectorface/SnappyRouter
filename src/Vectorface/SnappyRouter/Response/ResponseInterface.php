<?php

namespace Vectorface\SnappyRouter\Response;

/**
 * The interface that any response from the router will implement.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface ResponseInterface
{
    /**
     * Returns the serializable response object.
     * @return mixed The serializable response object.
     */
    public function getResponseObject();

    /**
     * Sets the serializable response object.
     * @param mixed $responseObject The serializable response object.
     * @return ResponseInterface Returns $this.
     */
    public function setResponseObject($responseObject);

    /**
     * Returns the HTTP status code associated with this response.
     * @return integer The HTTP status code associated with this response.
     */
    public function getStatusCode();

    /**
     * Sets the HTTP status code associated with this response.
     * @param int $statusCode The HTTP status code associated with this response.
     * @return ResponseInterface Returns $this.
     */
    public function setStatusCode($statusCode);
}
