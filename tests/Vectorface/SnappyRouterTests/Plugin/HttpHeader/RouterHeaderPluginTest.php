<?php

namespace Vectorface\SnappyRouterTests\Plugin\HttpHeader;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Handler\ControllerHandler;
use Vectorface\SnappyRouter\Plugin\HttpHeader\RouterHeaderPlugin;

/**
 * Tests the router header plugin.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class RouterHeaderPluginTest extends PHPUnit_Framework_TestCase
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
