<?php

namespace Vectorface\SnappyRouter\Request;

/**
 * A class representing an controller-modelled web request.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class HttpRequest extends AbstractRequest implements HttpRequestInterface
{
    /** Holds the HTTP verb used in the request */
    private $verb;

    /** Holds the contents of the various inputs ($_GET, $_POST, etc) */
    private $input;

    const INPUT_METHOD_QUERY = 'QUERY';
    const INPUT_METHOD_POST = 'POST';
    const INPUT_METHOD_PARAMS = 'PARAMS';

    /**
     * Constructor for a request.
     * @param string $controller The controller being requested.
     * @param string $action The action being invoked.
     * @param string $verb The HTTP verb used in the request.
     */
    public function __construct($controller, $action, $verb)
    {
        parent::__construct($controller, $action);
        $this->setVerb($verb);
        $this->input = array(
            self::INPUT_METHOD_QUERY  => array(),
            self::INPUT_METHOD_POST   => array(),
            self::INPUT_METHOD_PARAMS => array()
        );
    }

    /**
     * Returns the HTTP verb used in the request.
     * @return string The HTTP verb used in the request.
     */
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     * Sets the HTTP verb used in the request.
     * @param string $verb The HTTP verb used in the request.
     * @return RequestInterface Returns $this.
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;
        return $this;
    }

    /**
     * Returns the GET data parameter associated with the specified key.
     * @param string $param The GET data parameter.
     * @param mixed $defaultValue The default value to use when the key is not present.
     * @param array $filters The array of filters to apply to the data.
     * @return Returns the data from the GET parameter after being filtered (or
     *         the default value if the parameter is not present)
     */
    public function getQuery($param, $defaultValue = null, $filters = [])
    {
        return $this->fetchInputValue($this->queryData, $param, $defaultValue, $filters);
    }

    /**
     * Sets all the QUERY data for the current request.
     * @param array $queryData The query data for the current request.
     * @return Request Returns $this.
     */
    public function setQuery($queryData)
    {
        $this->input[self::INPUT_METHOD_QUERY];
        return $this;
    }

    /**
     * Returns the POST data parameter associated with the specified key.
     * @param string $param The POST data parameter.
     * @param mixed $defaultValue The default value to use when the key is not present.
     * @param array $filters The array of filters to apply to the data.
     * @return Returns the data from the POST parameter after being filtered (or
     *         the default value if the parameter is not present)
     */
    public function getPost($param, $defaultValue = null, $filters = [])
    {
        return $this->fetchInputValue($this->postData, $param, $defaultValue, $filters);
    }

    /**
     * Sets all the POST data for the current request.
     * @param array $postData The post data for the current request.
     * @return Request Returns $this.
     */
    public function setPost($postData)
    {
        $this->input[self::INPUT_METHOD_POST] = $postData;
        return $this;
    }

    /**
     * Returns all the route parameters for the current request.
     * @return array Returns the route parameters as an array.
     */
    public function getRouteParams()
    {
        return $this->input[self::INPUT_METHOD_PARAMS];
    }

    /**
     * Sets all the route parameters for the current request.
     * @param array $routeParams The route parameters for the request.
     * @return Request Returns $this.
     */
    public function setRouteParams($routeParams)
    {
        $this->input[self::INPUT_METHOD_PARAMS] = $routeParams;
        return $this;
    }

    /**
     * Returns an array of all the input parameters from the query and post data.
     * @return array An array of all input.
     */
    public function getAllInput()
    {
        return array_merge(
            $this->input[self::INPUT_METHOD_QUERY],
            $this->input[self::INPUT_METHOD_POST]
        );
    }

    /**
     * Fetches the input value from the given array, or the default value. Also
     * applies any requested filters.
     * @param array $array The array ($_POST, $_GET, $params, etc).
     * @param string $param The array key to lookup.
     * @param mixed $defaultValue The default value to use if the key is not
     *        found in the array.
     * @param array $filters The array of input filters to apply.
     * @return Returns the value filtered (or the default value filtered).
     */
    private function fetchInputValue($array, $param, $defaultValue, $filters)
    {
        $value = isset($array[$param]) ? $array[$param] : $defaultValue;
        return $this->applyInputFilters($value, $filters);
    }

    /**
     * Applies the array of filters against the input value.
     * @param mixed $value The input value.
     * @param array $filters The array of filters.
     * @return Returns the value after the filters have been applied.
     */
    private function applyInputFilters($value, $filters)
    {
        if (!is_array($filters)) {
            $filters = [$filters];
        }
        foreach ($filters as $filter) {
            switch ($filter) {
                case 'int':
                    $value = intval($value);
                    break;
                case 'float':
                    $value = floatval($value);
                    break;
                case 'trim':
                    $value = trim($value);
                    break;
                case 'lower':
                    $value = strtolower($value);
                    break;
                case 'upper':
                    $value = strtoupper($value);
                    break;
                case 'squeeze':
                    // removes lines containing only whitespace
                    $value = implode(PHP_EOL, array_filter(array_map('trim', explode(PHP_EOL, $value)), 'strlen'));
                    break;
            }
        }
        return $value;
    }
}
