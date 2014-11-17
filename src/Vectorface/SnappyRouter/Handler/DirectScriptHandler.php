<?php

namespace Vectorface\SnappyRouter\Handler;

/**
 * A handler for invoking scripts directly.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class DirectScriptHandler extends AbstractRequestHandler
{
    /** Options key for the path mapping array */
    const PATH_MAP = 'pathMap';

    private $scriptPath;

    /**
     * Returns true if the handler determines it should handle this request and false otherwise.
     * @param string $path The URL path for the request.
     * @param array $query The query parameters.
     * @param array $post The post data.
     * @param string $verb The HTTP verb used in the request.
     * @return Returns true if this handler will handle the request and false otherwise.
     */
    public function isAppropriate($path, $query, $post, $verb)
    {
        $options = $this->getOptions();
        $pathMaps = array();
        if (isset($options[self::PATH_MAP])) {
            $pathMaps = (array)$options[self::PATH_MAP];
        }
        foreach ($pathMaps as $pathPrefix => $folder) {
            if (false !== ($pos = strpos($path, $pathPrefix))) {
                $scriptPath = $folder.DIRECTORY_SEPARATOR.substr($path, $pos+strlen($pathPrefix));
                if (file_exists($scriptPath) && is_readable($scriptPath)) {
                    $this->scriptPath = realpath($scriptPath);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Performs the actual routing.
     * @return Returns the result of the route.
     */
    public function performRoute()
    {
        $buffer = ob_start();
        require $this->scriptPath;
        return ob_get_clean();
    }
}
