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

    private static $filterCallbacks = array(
        'int' => 'intval',
        'float' => 'floatval',
        'trim' => 'trim',
        'lower' => 'strtolower',
        'upper' => 'strtoupper',
        'squeeze' => array(__CLASS__, 'squeeze')
    );

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
     * Returns true if the request is a GET request and false otherwise.
     * @return bool Returns true if the request is a GET request and false
     *         otherwise.
     */
    public function isGet()
    {
        return ('GET' === strtoupper($this->getVerb()));
    }

    /**
     * Returns true if the request is a POST request and false otherwise.
     * @return bool Returns true if the request is a POST request and false
     *         otherwise.
     */
    public function isPost()
    {
        return ('POST' === strtoupper($this->getVerb()));
    }

    /**
     * Returns the GET data parameter associated with the specified key.
     * @param string $param The GET data parameter.
     * @param mixed $defaultValue The default value to use when the key is not present.
     * @param array $filters The array of filters to apply to the data.
     * @return Returns the data from the GET parameter after being filtered (or
     *         the default value if the parameter is not present)
     */
    public function getQuery($param, $defaultValue = null, $filters = array())
    {
        return $this->fetchInputValue(
            $this->input[self::INPUT_METHOD_QUERY],
            $param,
            $defaultValue,
            $filters
        );
    }

    /**
     * Sets all the QUERY data for the current request.
     * @param array $queryData The query data for the current request.
     * @return Request Returns $this.
     */
    public function setQuery($queryData)
    {
        $this->input[self::INPUT_METHOD_QUERY] = (array)$queryData;
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
    public function getPost($param, $defaultValue = null, $filters = array())
    {
        return $this->fetchInputValue(
            $this->input[self::INPUT_METHOD_POST],
            $param,
            $defaultValue,
            $filters
        );
    }

    /**
     * Sets all the POST data for the current request.
     * @param array $postData The post data for the current request.
     * @return Request Returns $this.
     */
    public function setPost($postData)
    {
        $this->input[self::INPUT_METHOD_POST] = (array)$postData;
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
     * @param string $value The input value.
     * @param array $filters The array of filters.
     * @return string Returns the value after the filters have been applied.
     */
    private function applyInputFilters($value, $filters)
    {
        if (!is_array($filters)) {
            $filters = array($filters);
        }
        foreach ($filters as $filter) {
            if (isset(self::$filterCallbacks[$filter])) {
                $value = call_user_func(self::$filterCallbacks[$filter], $value);
            }
        }
        return $value;
    }

    /**
     * Takes a string and removes empty lines.
     * @param string $string The input string.
     * @return string Returns the string with empty lines removed.
     */
    private static function squeeze($string)
    {
        return implode(
            PHP_EOL,
            array_filter(
                array_map(
                    'trim',
                    explode(PHP_EOL, $string)
                ),
                'strlen'
            )
        );
    }
}
