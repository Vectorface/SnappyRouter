<?php

namespace Vectorface\SnappyRouterTests\Task;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Exception\PluginException;
use Vectorface\SnappyRouter\Exception\ResourceNotFoundException;
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
     *
     * @throws PluginException
     */
    public function testSynopsis()
    {
        $options = [
            Config::KEY_TASKS => [
                'TestTask' => DummyTestTask::class,
            ]
        ];
        $handler = new CliTaskHandler($options);
        $components = [
            'dummyScript.php',
            '--task',
            'TestTask',
            '--action',
            'testAction'
        ];

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
     *
     * @throws PluginException|ResourceNotFoundException
     */
    public function testMissingActionOnTask()
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage("TestTask task does not have action missingAction.");

        $options = [
            Config::KEY_TASKS => [
                'TestTask' => DummyTestTask::class,
            ]
        ];
        $handler = new CliTaskHandler($options);
        $components = [
            'dummyScript.php',
            '--task',
            'TestTask',
            '--action',
            'missingAction'
        ];
        $this->assertTrue($handler->isAppropriate($components));
        $handler->performRoute();
    }
}
