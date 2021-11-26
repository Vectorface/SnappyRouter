<?php

namespace Vectorface\SnappyRouterTests\Handler;

use Exception;
use ReflectionClass;
use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Exception\EncoderException;
use Vectorface\SnappyRouter\Exception\PluginException;
use Vectorface\SnappyRouter\Exception\ResourceNotFoundException;
use Vectorface\SnappyRouter\Handler\JsonRpcHandler;
use Vectorface\SnappyRouterTests\Controller\TestDummyController;

/**
 * A test for the JsonRpcHandler class.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 */
class JsonRpcHandlerTest extends TestCase
{
    /**
     * Helper method to override the internals of php://input for test purposes.
     * @param JsonRpcHandler $handler The handler to override.
     * @param mixed $payload The payload to hand off for the request input.
     */
    public static function setRequestPayload(JsonRpcHandler $handler, $payload)
    {
        $tmpfile = tempnam(sys_get_temp_dir(), __CLASS__);
        file_put_contents($tmpfile, is_string($payload) ? $payload : json_encode($payload));

        $refCls = new ReflectionClass($handler);
        $prop = $refCls->getProperty('stdin');
        $prop->setAccessible(true);
        $prop->setValue($handler, $tmpfile);
    }

    /**
     * An overview of how to use the JsonRpcHandler class.
     *
     * @throws PluginException|EncoderException|ResourceNotFoundException
     */
    public function testSynopsis()
    {
        $options = [
            Config::KEY_CONTROLLERS => [
                'TestController' => TestDummyController::class,
            ]
        ];
        $handler = new JsonRpcHandler($options);

        /* Ignores unconfigured services and anything other than POST */
        $this->assertFalse($handler->isAppropriate('/anything', [], [], 'GET'));
        $this->assertFalse($handler->isAppropriate('/anything', [], [], 'POST'));

        /* A single JSON-RPC 1.0 request. */
        $this->setRequestPayload($handler, ['method' => 'testAction', 'id' => '1']);
        $this->assertTrue($handler->isAppropriate('/x/y/z/TestController', [], [], 'POST'));

        $result = json_decode($handler->performRoute());
        $this->assertEquals("This is a test service.", $result->result);
        $this->assertEquals(1, $result->id);

        /* A batch of JSON-RPC 2.0 requests. */
        $this->setRequestPayload($handler, [
            ['jsonrpc' => '2.0', 'method' => 'testAction', 'id' => '1'],
            ['jsonrpc' => '2.0', 'method' => 'testAction', 'id' => '2']
        ]);
        $this->assertTrue($handler->isAppropriate('/x/y/z/TestController', [], [], 'POST'));
        $this->assertCount(2, $handler->getRequests());

        $result = json_decode($handler->performRoute());
        $this->assertCount(2, $result, "Expect 2 responses for 2 calls");
        $this->assertEquals("2.0", $result[0]->jsonrpc);
        $this->assertEquals("This is a test service.", $result[0]->result);
        $this->assertEquals("1", $result[0]->id);
        $this->assertEquals("2.0", $result[1]->jsonrpc);
        $this->assertEquals("This is a test service.", $result[1]->result);
        $this->assertEquals("2", $result[1]->id);

        /* Handles notifications without replying. */
        $this->setRequestPayload($handler, [
            ['jsonrpc' => '2.0', 'method' => 'testAction', 'id' => '1'],
            ['jsonrpc' => '2.0', 'method' => 'testAction'] // Notification
        ]);
        $this->assertTrue($handler->isAppropriate('/x/y/z/TestController', [], [], 'POST'));
        $result = json_decode($handler->performRoute());
        $this->assertCount(1, $result, "Expect 1 response for 1 call and 1 notification");
        $this->assertEquals("1", $result[0]->id);
    }

    /**
     * Tests various edge cases of the JsonRpcHandler::isAppropriate method.
     *
     * @throws PluginException
     */
    public function testIsAppropriate()
    {
        /* Without a base path, only the last path element is used to map the controller/service */
        $options = [
            Config::KEY_CONTROLLERS => [
                'TestController' => TestDummyController::class,
            ]
        ];
        $handler = new JsonRpcHandler($options);

        /* Ignores non-JSON POST'ed data */
        $this->setRequestPayload($handler, "<?xml version=\"1.0\" encoding=\"UTF-8\"?><document></document>");
        $this->assertFalse($handler->isAppropriate('/x/y/z/TestController', [], [], 'POST'));

        /* With a base path, a longer controller key can be used */
        $options = [
            JsonRpcHandler::KEY_BASE_PATH => 'x/y/z',
            Config::KEY_CONTROLLERS       => [
                'Test/TestController' => TestDummyController::class,
            ]
        ];
        $handler = new JsonRpcHandler($options);
        $this->setRequestPayload($handler, []);
        $this->assertTrue($handler->isAppropriate("/x////y//z/Test/TestController.php", [], [], 'POST'));
    }

    /**
     * Tests the edge cases of the JsonRpcHandler::performRoute method.
     *
     * @throws EncoderException|PluginException|ResourceNotFoundException
     */
    public function testPerformRoute()
    {
        $options = [
            Config::KEY_CONTROLLERS => [
                'TestController' => TestDummyController::class,
            ]
        ];
        $handler = new JsonRpcHandler($options);

        /* Blows up safely for invalid requests. */
        $this->setRequestPayload($handler, ['id' => '1']);
        $this->assertTrue($handler->isAppropriate('/x/y/z/TestController', [], [], 'POST'));
        $result = json_decode($handler->performRoute());
        $this->assertFalse(isset($result->result));
        $this->assertEquals(-32600, $result->error->code);

        /* Blows up safely for internal errors */
        $this->setRequestPayload($handler, ['method' => 'genericExceptionAction', 'id' => '1']);
        $this->assertTrue($handler->isAppropriate('/x/y/z/TestController', [], [], 'POST'));
        $result = json_decode($handler->performRoute());
        $this->assertFalse(isset($result->result));
        $this->assertEquals('A generic exception.', $result->error->message);
        $this->assertTrue(-32000 >= $result->error->code);
    }

    /**
     * Tests the method JsonRpcHandler::handleException.
     *
     * @throws PluginException
     */
    public function testHandleException()
    {
        $handler = new JsonRpcHandler([]);
        /* Any internal error should be wrapped in a JSON-RPC exception. */
        $internal = $handler->handleException(new Exception("foo", 123));
        $this->assertTrue($internal->error->code <= -32000);

        /* Any JSON-RPC exception (code <= 32000) can pass through */
        $passthrough = $handler->handleException(new Exception("passthrough", -32001));
        $this->assertEquals(-32001, $passthrough->error->code);
        $this->assertEquals("passthrough", $passthrough->error->message);
    }

    /**
     * Tests that a request to a service that doesn't exist returns a 404
     * response.
     *
     * @throws EncoderException|PluginException|ResourceNotFoundException
     */
    public function testServiceNotFound()
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage("No such service: nonexistent");

        $handler = new JsonRpcHandler([]);
        $this->setRequestPayload($handler, ['method' => 'someMethod', 'id' => '1']);
        $this->assertTrue($handler->isAppropriate('/nonexistent', [], [], 'POST'));
        $handler->performRoute();
    }
}
