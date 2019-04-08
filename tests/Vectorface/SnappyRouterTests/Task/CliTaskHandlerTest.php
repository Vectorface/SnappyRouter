<?php

namespace Vectorface\SnappyRouterTests\Task;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Handler\CliTaskHandler;

/**
 * Tests the CliTaskHandler.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class CliTaskHandlerTest extends TestCase
{
    /**
     * An overview of how to use the CliTaskHandler class.
     * @test
     */
    public function synopsis()
    {
        $options = array(
            Config::KEY_TASKS => array(
                'TestTask' => 'Vectorface\SnappyRouterTests\Task\DummyTestTask'
            )
        );
        $handler = new CliTaskHandler($options);
        $components = array(
            'dummyScript.php',
            '--task',
            'TestTask',
            '--action',
            'testAction'
        );

        // the components needs to be at least 5 elements with --task and --action
        $this->assertTrue($handler->isAppropriate($components));

        // assert the handler is not appropriate if we only have 4 elements
        $this->assertFalse($handler->isAppropriate(array_slice($components, 0, 4)));

        // assert the handler is not appropriate if --task and --action are missing
        $badComponents = $components;
        $badComponents[1] = '--service';
        $this->assertFalse($handler->isAppropriate($badComponents));
    }

    /**
     * A test that asserts an exception is thrown if we call an action missing
     * from a registered task.
     * @expectedException Vectorface\SnappyRouter\Exception\ResourceNotFoundException
     * @expectedExceptionMessage TestTask task does not have action missingAction.
     */
    public function testMissingActionOnTask()
    {
        $options = array(
            Config::KEY_TASKS => array(
                'TestTask' => 'Vectorface\SnappyRouterTests\Task\DummyTestTask'
            )
        );
        $handler = new CliTaskHandler($options);
        $components = array(
            'dummyScript.php',
            '--task',
            'TestTask',
            '--action',
            'missingAction'
        );
        $this->assertTrue($handler->isAppropriate($components));
        $handler->performRoute();
    }
}
