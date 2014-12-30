<?php

namespace Vectorface\SnappyRouterTests\Handler;

use \Exception;
use \ReflectionClass;
use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Handler\JsonRpcHandler;

/**
 * A test for the JsonRpcHandler class.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 */
class JsonRpcHandlerTest extends PHPUnit_Framework_TestCase
{
    private function setRequestPayload(JsonRpcHandler $handler, $payload)
    {
        $tmpfile = tempnam(sys_get_temp_dir(), __CLASS__);
        file_put_contents($tmpfile, is_string($payload) ? $payload : json_encode($payload));

        $refCls = new ReflectionClass($handler);
        $prop = $refCls->getProperty('stdin');
        $prop->setAccessible(true);
        $prop->setValue($handler, $tmpfile);

        $this->tmpfile = $tmpfile;
    }

    /**
     * An overview of how to use the JsonRpcHandler class.
     * @test
     */
    public function synopsis()
    {
        $options = array(
            Config::KEY_CONTROLLERS => array(
                'TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
            )
        );
        $handler = new JsonRpcHandler($options);

        /* Ignores unconfigured services and anything other than POST */
        $this->assertFalse($handler->isAppropriate('/anything', array(), array(), 'GET'));
        $this->assertFalse($handler->isAppropriate('/anything', array(), array(), 'POST'));

        /* A single JSON-RPC 1.0 request. */
        $this->setRequestPayload($handler, array('method' => 'testAction', 'id' => '1'));
        $this->assertTrue($handler->isAppropriate('/x/y/z/TestController', array(), array(), 'POST'));

        $result = json_decode($handler->performRoute());
        $this->assertEquals("This is a test service.", $result->result);
        $this->assertEquals(1, $result->id);

        /* A batch of JSON-RPC 2.0 requests. */
        $this->setRequestPayload($handler, array(
            array('jsonrpc' => '2.0', 'method' => 'testAction', 'id' => '1'),
            array('jsonrpc' => '2.0', 'method' => 'testAction', 'id' => '2')
        ));
        $this->assertTrue($handler->isAppropriate('/x/y/z/TestController', array(), array(), 'POST'));

        $result = json_decode($handler->performRoute());
        $this->assertEquals(2, count($result), "Expect 2 responses for 2 calls");
        $this->assertEquals("2.0", $result[0]->jsonrpc);
        $this->assertEquals("This is a test service.", $result[0]->result);
        $this->assertEquals("1", $result[0]->id);
        $this->assertEquals("2.0", $result[1]->jsonrpc);
        $this->assertEquals("This is a test service.", $result[1]->result);
        $this->assertEquals("2", $result[1]->id);

        /* Handles notifications without replying. */
        $this->setRequestPayload($handler, array(
            array('jsonrpc' => '2.0', 'method' => 'testAction', 'id' => '1'),
            array('jsonrpc' => '2.0', 'method' => 'testAction') // Notification
        ));
        $this->assertTrue($handler->isAppropriate('/x/y/z/TestController', array(), array(), 'POST'));
        $result = json_decode($handler->performRoute());
        $this->assertEquals(1, count($result), "Expect 1 response for 1 call and 1 notification");
        $this->assertEquals("1", $result[0]->id);
    }

    public function testIsAppropriate()
    {
        /* Without a base path, only the last path element is used to map the controller/service */
        $options = array(
            Config::KEY_CONTROLLERS => array(
                'TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
            )
        );
        $handler = new JsonRpcHandler($options);

        /* Ignores unconfigured services */
        $this->assertFalse($handler->isAppropriate('/anything', array(), array(), 'POST'));

        /* Ignores non-JSON POST'ed data */
        $this->setRequestPayload($handler, "<?xml version=\"1.0\" encoding=\"UTF-8\"?><document></document>");
        $this->assertFalse($handler->isAppropriate('/x/y/z/TestController', array(), array(), 'POST'));

        /* With a base path, a longer controller key can be used */
        $options = array(
            JsonRpcHandler::KEY_BASE_PATH => 'x/y/z',
            Config::KEY_CONTROLLERS => array(
                'Test/TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
            )
        );
        $handler = new JsonRpcHandler($options);
        $this->setRequestPayload($handler, array());
        $this->assertTrue($handler->isAppropriate("/x////y//z/Test/TestController.php", array(), array(), 'POST'));

    }

    public function testPerformRoute()
    {
        $options = array(
            Config::KEY_CONTROLLERS => array(
                'TestController' => 'Vectorface\\SnappyRouterTests\\Controller\\TestDummyController'
            )
        );
        $handler = new JsonRpcHandler($options);

        /* Blows up safely for invalid requests. */
        $this->setRequestPayload($handler, array('id' => '1'));
        $this->assertTrue($handler->isAppropriate('/x/y/z/TestController', array(), array(), 'POST'));
        $result = json_decode($handler->performRoute());
        $this->assertFalse(isset($result->result));
        $this->assertEquals(-32600, $result->error->code);

        /* Blows up safely for internal errors */
        $this->setRequestPayload($handler, array('method' => 'genericExceptionAction', 'id' => '1'));
        $this->assertTrue($handler->isAppropriate('/x/y/z/TestController', array(), array(), 'POST'));
        $result = json_decode($handler->performRoute());
        $this->assertFalse(isset($result->result));
        $this->assertEquals('A generic exception.', $result->error->message);
        $this->assertTrue(-32000 >= $result->error->code);
    }

    public function testHandleException()
    {
        $handler = new JsonRpcHandler(array());
        /* Any internal error should be wrapped in a JSON-RPC exception. */
        $internal = $handler->handleException(new Exception("foo", 123));
        $this->assertTrue($internal->error->code <= -32000);

        /* Any JSON-RPC exception (code <= 32000) can pass through */
        $passthrough = $handler->handleException(new Exception("passthrough", -32001));
        $this->assertEquals(-32001, $passthrough->error->code);
        $this->assertEquals("passthrough", $passthrough->error->message);
    }
}
