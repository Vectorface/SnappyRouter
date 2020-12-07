<?php

namespace Vectorface\SnappyRouterTests\Response;

use Exception;
use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Request\JsonRpcRequest;
use Vectorface\SnappyRouter\Response\JsonRpcResponse;

/**
 * Tests the JsonRpcResponse class.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 */
class JsonRpcResponseTest extends TestCase
{
    /**
     * An overview of how to use the JsonRpcResponse class.
     */
    public function testSynopsis()
    {
        /* Needs to be based on a related request */
        $request = new JsonRpcRequest('MyService', (object)[
            'jsonrpc' => '2.0',
            'method'  => 'remoteProcedure',
            'params'  => [1, 2, 3],
            'id'      => 'identifier'
        ]);

        $response = new JsonRpcResponse('object, array, or scalar', null, $request);
        $obj = $response->getResponseObject();
        $this->assertEquals("2.0", $obj->jsonrpc, "Responds with the same version");
        $this->assertEquals('object, array, or scalar', $obj->result, "Result is passed through");
        $this->assertEquals("identifier", $obj->id, "Request ID is passed back");

        /* Notifications generate no response */
        $request = new JsonRpcRequest('MyService', (object)['method' => 'notifyProcedure']);
        $response = new JsonRpcResponse("anything", null, $request);
        $this->assertEquals("", $response->getResponseObject());

        /* An error passes back a message and a code */
        $request = new JsonRpcRequest('MyService', (object)['method' => 'any', 'id' => 123]);
        $response = new JsonRpcResponse(null, new Exception("ex", 123), $request);
        $obj = $response->getResponseObject();
        $this->assertEquals(123, $obj->error->code);
        $this->assertEquals("ex", $obj->error->message);
    }
}
