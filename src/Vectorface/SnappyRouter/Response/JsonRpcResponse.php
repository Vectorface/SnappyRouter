<?php

namespace Vectorface\SnappyRouter\Response;

use stdClass;
use Exception;
use Vectorface\SnappyRouter\Request\JsonRpcRequest;

/**
 * The response to be returned to the client for JSON-RPC requests.
 *
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 */
class JsonRpcResponse extends Response
{
    /**
     * Construct a Response from JSON-RPC style result, error, and JSON-RPC request.
     *
     * @param mixed $result The result of the remote procedure call.
     * @param Exception $error The error, as an exception. (Requires code and message.)
     * @param JsonRpcRequest $request The request that led to the generation of this response.
     */
    public function __construct($result, Exception $error = null, JsonRpcRequest $request = null)
    {
        $response = new stdClass();

        /* JSON-RPC spec: either error or result, never both. */
        if ($error) {
            $response->error = (object)[
                'code'    => $error->getCode(),
                'message' => $error->getMessage()
            ];
        } else {
            $response->result = $result;
        }

        if ($request) {
            /* 1.0: omit version, 2.0 and newer, echo back version. */
            if ($request->getVersion() != "1.0") {
                $response->jsonrpc = $request->getVersion();
            }

            /* For notifications (null id), return nothing. Otherwise, pass back the id. */
            if ($request->getIdentifier() === null) {
                $response = "";
            } else {
                $response->id = $request->getIdentifier();
            }
        }

        parent::__construct($response);
    }
}
