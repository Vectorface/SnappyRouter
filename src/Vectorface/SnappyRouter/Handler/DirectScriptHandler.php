<?php

namespace Vectorface\SnappyRouter\Handler;

use Vectorface\SnappyRouter\Request\HttpRequest;

/**
 * A handler for invoking scripts directly.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class DirectScriptHandler extends AbstractRequestHandler
{
    /** Options key for the path mapping array */
    const KEY_PATH_MAP = 'pathMap';

    private $scriptPath;

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
        $options = $this->getOptions();
        $pathMaps = [];
        if (isset($options[self::KEY_PATH_MAP])) {
            $pathMaps = (array)$options[self::KEY_PATH_MAP];
        }
        foreach ($pathMaps as $pathPrefix => $folder) {
            if (false !== ($pos = strpos($path, $pathPrefix))) {
                $scriptPath = $folder.DIRECTORY_SEPARATOR.substr($path, $pos + strlen($pathPrefix));
                if (file_exists($scriptPath) && is_readable($scriptPath)) {
                    $this->scriptPath = realpath($scriptPath);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns a request object extracted from the request details (path, query, etc). The method
     * isAppropriate() must have returned true, otherwise this method should return null.
     * @return HttpRequest|null Returns a Request object or null if this handler is not appropriate.
     */
    public function getRequest()
    {
        return null;
    }

    /**
     * Performs the actual routing.
     * @return string Returns the result of the route.
     */
    public function performRoute()
    {
        ob_start();
        require $this->scriptPath;
        return ob_get_clean();
    }
}
