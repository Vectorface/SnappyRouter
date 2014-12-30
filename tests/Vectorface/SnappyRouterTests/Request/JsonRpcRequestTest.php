<?php

namespace Vectorface\SnappyRouterTests\Request;

use Vectorface\SnappyRouter\Request\JsonRpcRequest;

/**
 * Tests the JsonRpcRequest class.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 */
class JsonRpcRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * An overview of how to use the JsonRpcRequest class.
     * @test
     */
    public function synopsis()
    {
        /* Handles JSON-RPC 1.0 requests. */
        $request = new JsonRpcRequest('MyService', (object)array(
            'method' => 'remoteProcedure',
            'params' => array(1, 2, 3),
            'id' => 'uniqueidentifier'
        ));

        $this->assertEquals('POST', $request->getVerb());
        $this->assertEquals('remoteProcedure', $request->getMethod());
        $this->assertEquals('1.0', $request->getVersion());
        $this->assertEquals(array(1, 2, 3), $request->getParameters());
        $this->assertEquals('uniqueidentifier', $request->getIdentifier());
        $this->assertNull($request->getPost('anything')); // Post should be ignored.

        /* Handles JSON-RPC 2.0 requests. */
        $request = new JsonRpcRequest('MyService', (object)array(
            'jsonrpc' => '2.0',
            'method' => 'remoteProcedure',
            'id' => 'uniqueidentifier'
        ));

        $this->assertEquals('2.0', $request->getVersion());
        $this->assertEquals(array(), $request->getParameters());

        /* Catches invalid request format. */
        try {
            new JsonRpcRequest('MyService', (object)array(
                'jsonrpc' => '2.0',
                'method' => null
            ));
            $this->fail("Empty method should not be allowed.");
        } catch (\Exception $e) {
            $this->assertEquals(-32600, $e->getCode()); // "Invalid Request"
        }
    }
}
