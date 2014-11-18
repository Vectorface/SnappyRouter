<?php

namespace Vectorface\SnappyRouter\Handler;

use \Exception;
use Vectorface\SnappyRouter\Exception\ResourceNotFoundException;
use Vectorface\SnappyRouter\Task\TaskInterface;

/**
 * A CLI handler for task/action scripts.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class CliTaskHandler extends AbstractCliHandler
{
    public function isAppropriate($components)
    {
        $components = array_values(array_filter(array_map('trim', $components), 'strlen'));
        $this->options = array();
        if (count($components) < 5) {
            return false;
        }

        if ($components[1] !== '--task' || $components[3] !== '--action') {
            return false;
        }
        $this->options['task'] = $components[2];
        $this->options['action'] = $components[4];

        try {
            // ensure we have this task registered
            $this->getServiceProvider()->get($this->options['task']);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function performRoute()
    {
        $task = $this->getServiceProvider()->getServiceInstance($this->options['task']);
        if (false === method_exists($task, $this->options['action'])) {
            throw new ResourceNotFoundException(
                sprintf(
                    '%s task does not have action %s.',
                    $this->options['task'],
                    $this->options['action']
                )
            );
        }

        // call the task's init function
        if ($task instanceof TaskInterface) {
            $task->init($this->options);
        }

        $taskParams = array_splice($_SERVER['argv'], 5);
        $action = $this->options['action'];
        return $task->$action($taskParams);
    }
}
