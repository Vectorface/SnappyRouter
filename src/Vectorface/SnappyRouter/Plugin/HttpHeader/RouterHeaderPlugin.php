<?php

namespace Vectorface\SnappyRouter\Plugin\HttpHeader;

use Vectorface\SnappyRouter\Handler\AbstractHandler;
use Vectorface\SnappyRouter\Plugin\AbstractPlugin;

/**
 * A plugin that adds an HTTP header to indicate the route passed through the service router.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class RouterHeaderPlugin extends AbstractPlugin
{
    /**
     * Invoked directly after the router decides which handler will be used.
     * @param AbstractHandler $handler The handler selected by the router.
     */
    public function afterhandlerSelected(AbstractHandler $handler)
    {
        parent::afterhandlerSelected($handler);
        @header('X-Router: SnappyRouter');
    }
}
