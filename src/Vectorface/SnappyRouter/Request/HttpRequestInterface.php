<?php

namespace Vectorface\SnappyRouter\Request;

/**
 * The interface for Http requests.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface HttpRequestInterface
{
    /**
     * Returns the HTTP verb used in the request.
     * @return string The HTTP verb used in the request.
     */
    public function getVerb();

    /**
     * Sets the HTTP verb used in the request.
     * @param string $verb The HTTP verb used in the request.
     * @return HttpRequestInterface Returns $this.
     */
    public function setVerb($verb);

    /**
     * Returns the GET data parameter associated with the specified key.
     * @param string $param The GET data parameter.
     * @param mixed $defaultValue The default value to use when the key is not present.
     * @param array $filters The array of filters to apply to the data.
     * @return mixed Returns the data from the GET parameter after being filtered (or
     *         the default value if the parameter is not present)
     */
    public function getQuery($param, $defaultValue = null, $filters = []);

    /**
     * Sets all the QUERY data for the current request.
     * @param array $queryData The query data for the current request.
     * @return HttpRequestInterface Returns $this.
     */
    public function setQuery($queryData);

    /**
     * Returns the POST data parameter associated with the specified key.
     * @param string $param The POST data parameter.
     * @param mixed $defaultValue The default value to use when the key is not present.
     * @param array $filters The array of filters to apply to the data.
     * @return mixed Returns the data from the POST parameter after being filtered (or
     *         the default value if the parameter is not present)
     */
    public function getPost($param, $defaultValue = null, $filters = []);

    /**
     * Sets all the POST data for the current request.
     * @param array $postData The post data for the current request.
     * @return HttpRequestInterface Returns $this.
     */
    public function setPost($postData);

    /**
     * Returns an array of all the input parameters from the query and post data.
     * @return array An array of all input.
     */
    public function getAllInput();
}
