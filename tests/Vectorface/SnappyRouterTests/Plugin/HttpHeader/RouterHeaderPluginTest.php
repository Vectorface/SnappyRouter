<?php

namespace Vectorface\SnappyRouterTests\Plugin\HttpHeader;

use PHPUnit\Framework\TestCase;
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
     * @test
     */
    public function synopsis()
    {
        $handler = new ControllerHandler(array());
        $plugin = new RouterHeaderPlugin(array());
        $this->assertNull($plugin->afterhandlerSelected($handler));
    }
}
