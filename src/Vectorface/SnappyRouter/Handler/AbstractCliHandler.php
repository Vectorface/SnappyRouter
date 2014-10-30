<?php

namespace Vectorface\SnappyRouter\Handler;

/**
 * The base class for all handlers for CLI routes.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractCliHandler extends AbstractHandler
{

    /** constant options key for the list of tasks */
    const KEY_TASKS = 'tasks';

    /**
     * Constructor for the class.
     * @param array $options An array of options for the plugin.
     */
    public function __construct($options)
    {
        if (isset($options[self::KEY_TASKS])) {
            $options[self::KEY_SERVICES] = $options[self::KEY_TASKS];
            unset($options[self::KEY_TASKS]);
        }
        parent::__construct($options);
    }

    /**
     * Determines whether the current handler is appropriate for the given
     * path components.
     * @param array $components The path components as an array.
     * @return bool Returns true if the handler is appropriate and false
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
