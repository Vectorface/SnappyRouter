<?php

namespace Vectorface\SnappyRouter\Handler;

use Vectorface\SnappyRouter\Config\Config;

/**
 * The base class for all handlers for CLI routes.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractCliHandler extends AbstractHandler
{
    /**
     * Constructor for the class.
     * @param array $options An array of options for the plugin.
     */
    public function __construct($options)
    {
        if (isset($options[Config::KEY_TASKS])) {
            $options[Config::KEY_SERVICES] = $options[Config::KEY_TASKS];
            unset($options[Config::KEY_TASKS]);
        }
        parent::__construct($options);
    }

    /**
     * Determines whether the current handler is appropriate for the given
     * path components.
     * @param array $components The path components as an array.
     * @return boolean Returns true if the handler is appropriate and false
     *         otherwise.
     */
    abstract public function isAppropriate($components);

    /**
     * Returns whether a handler should function in a CLI environment.
     * @return bool Returns true if the handler should function in a CLI
     *         environment and false otherwise.
     */
    public function isCliHandler()
    {
        return true;
    }
}
