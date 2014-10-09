<?php

namespace VectorFace\SnappyRouter\Response;

/**
 * The interface that any response from the router will implement.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface ResponseInterface
{
    /**
     * Returns the serializeable response object.
     * @return The serializeable response object.
     */
    public function getResponseObject();

    /**
     * Sets the serializeable response object.
     * @param mixed $responseObject The serializeable response object.
     * @return Returns $this.
     */
    public function setResponseObject($responseObject);

    /**
     * Returns the HTTP status code associated with this response.
     * @return The HTTP status code associated with this response.
     */
    public function getStatusCode();

    /**
     * Sets the HTTP status code associated with this response.
     * @param int $statusCode The HTTP status code associated with this response.
     * @return Returns $this.
     */
    public function setStatusCode($statusCode);
}