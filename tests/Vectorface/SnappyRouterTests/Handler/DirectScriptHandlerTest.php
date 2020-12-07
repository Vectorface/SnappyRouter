<?php

namespace Vectorface\SnappyRouterTests\Handler;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Exception\PluginException;
use Vectorface\SnappyRouter\Handler\DirectScriptHandler;

/**
 * Tests the DirectScriptHandler class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class DirectScriptHandlerTest extends TestCase
{
    /**
     * An overview of how to use the class.
     *
     * @throws PluginException
     */
    public function testSynopsis()
    {
        // the configuration maps a path like /cgi-bin to this folder
        $config = array(
            DirectScriptHandler::KEY_PATH_MAP => array(
                '/cgi-bin' => __DIR__
            )
        );
        $handler = new DirectScriptHandler($config);
        $path = '/cgi-bin/test_script.php';
        // the file itself exists so we should get back true
        $this->assertTrue(
            $handler->isAppropriate($path, array(), array(), 'GET')
        );
        // the test script simply has `echo "Hello world!"`
        $expected = 'Hello world!';
        $this->assertEquals($expected, $handler->performRoute());

        // the script is not found so the handler should not be marked as
        // appropriate
        $path = '/cgi-bin/script_not_found.php';
        $this->assertFalse(
            $handler->isAppropriate($path, array(), array(), 'GET')
        );
    }

    /**
     * Tests that the getRequest() method returns null.
     *
     * @throws PluginException
     */
    public function testGetRequest()
    {
        $config = array(
            DirectScriptHandler::KEY_PATH_MAP => array(
                '/cgi-bin' => __DIR__
            )
        );
        $handler = new DirectScriptHandler($config);
        $this->assertTrue($handler->isAppropriate('/cgi-bin/test_script.php', array(), array(), 'GET'));
        $this->assertNull($handler->getRequest());
    }
}
