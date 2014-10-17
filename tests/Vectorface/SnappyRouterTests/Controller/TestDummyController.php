<?php

namespace Vectorface\SnappyRouterTests\Controller;

use \Exception;
use Vectorface\SnappyRouter\Controller\AbstractController;

/**
 * A test controller for testing the router.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class TestDummyController extends AbstractController
{
    public function testAction()
    {
        return 'This is a test service.';
    }

    public function genericExceptionAction()
    {
        throw new Exception('A generic exception.');
    }
}
