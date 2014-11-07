<?php

namespace Vectorface\SnappyRouter\Task;

/**
 * The interface implemented by all routed cli tasks.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
interface TaskInterface
{
    /**
     * Initializes the cli task from the given configuration.
     * @param array $options The task options.
     */
    public function init($options);
}
