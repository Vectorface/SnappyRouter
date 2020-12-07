<?php

namespace Vectorface\SnappyRouterTests\Plugin\HttpHeader;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Exception\PluginException;
use Vectorface\SnappyRouter\Handler\ControllerHandler;
use Vectorface\SnappyRouter\Plugin\HttpHeader\RouterHeaderPlugin;

/**
 * Tests the router header plugin.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class RouterHeaderPluginTest extends TestCase
{
    /**
     * An overview of how to use the plugin.
     *
     * @throws PluginException
     */
    public function testSynopsis()
    {
        $handler = new ControllerHandler([]);
        $plugin = new RouterHeaderPlugin([]);
        $plugin->afterhandlerSelected($handler);
    }
}
