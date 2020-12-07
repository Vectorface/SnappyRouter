<?php

namespace Vectorface\SnappyRouterTests\Request;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Request\JsonRpcRequest;

/**
 * Tests the JsonRpcRequest class.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 */
class JsonRpcRequestTest extends TestCase
{
    /**
     * An overview of how to use the JsonRpcRequest class.
     */
    public function testSynopsis()
    {
        /* Handles JSON-RPC 1.0 requests. */
        $request = new JsonRpcRequest('MyService', (object)[
            'method' => 'remoteProcedure',
            'params' => [1, 2, 3],
            'id'     => 'uniqueidentifier'
        ]);

        $this->assertEquals('POST', $request->getVerb());
        $this->assertEquals('remoteProcedure', $request->getMethod());
        $this->assertEquals('1.0', $request->getVersion());
        $this->assertEquals([1, 2, 3], $request->getParameters());
        $this->assertEquals('uniqueidentifier', $request->getIdentifier());
        $this->assertNull($request->getPost('anything')); // Post should be ignored.

        /* Handles JSON-RPC 2.0 requests. */
        $request = new JsonRpcRequest('MyService', (object)[
            'jsonrpc' => '2.0',
            'method'  => 'remoteProcedure',
            'id'      => 'uniqueidentifier'
        ]);

        $this->assertEquals('2.0', $request->getVersion());
        $this->assertEquals([], $request->getParameters());

        /* Catches invalid request format. */
        $request = new JsonRpcRequest('MyService', (object)[
            'jsonrpc' => '2.0',
            'method'  => null
        ]);
        $this->assertFalse($request->isValid());
    }
}
