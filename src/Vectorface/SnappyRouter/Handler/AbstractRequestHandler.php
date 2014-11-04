<?php

namespace Vectorface\SnappyRouter\Handler;

use \Exception;
use Vectorface\SnappyRouter\Exception\RouterExceptionInterface;
use Vectorface\SnappyRouter\Response\AbstractResponse;

/**
 * The base class for all handlers that implement a request/response style of
 * invokation.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
abstract class AbstractRequestHandler extends AbstractHandler implements RequestHandlerInterface
{
    /**
     * Provides the handler with an opportunity to perform any last minute
     * error handling logic. The returned value will be serialized by the
     * handler's encoder.
     * @param Exception $e The exception that was thrown.
     * @return Returns a serializable value that will be encoded and returned
     *         to the client.
     */
    public function handleException(Exception $e)
    {
        $responseCode = AbstractResponse::RESPONSE_SERVER_ERROR;
        if ($e instanceof RouterExceptionInterface) {
            $responseCode = $e->getAssociatedStatusCode();
        }
        \Vectorface\SnappyRouter\http_response_code($responseCode);
        return parent::handleException($e);
    }

    /**
     * Returns whether a handler should function in a CLI environment.
     * @return bool Returns true if the handler should function in a CLI
     *         environment and false otherwise.
     */
    public function isCliHandler()
    {
        return false;
    }
}
