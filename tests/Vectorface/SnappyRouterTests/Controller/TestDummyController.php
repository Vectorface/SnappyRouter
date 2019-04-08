<?php

namespace Vectorface\SnappyRouterTests\Controller;

use \Exception;
use Vectorface\SnappyRouter\Controller\AbstractController;
use Vectorface\SnappyRouter\Exception\InternalErrorException;

/**
 * A test controller for testing the router.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class TestDummyController extends AbstractController
{

    public function indexAction()
    {
    }

    public function testAction()
    {
        return 'This is a test service.';
    }

    public function genericExceptionAction()
    {
        throw new InternalErrorException('A generic exception.');
    }

    public function defaultAction()
    {
        // ensure some abstract methods work
        $this->set('request', $this->getRequest());
        $this->get('request');
    }

    public function arrayAction()
    {
        $this->viewContext['variable'] = 'broken';
        return array('variable' => 'test');
    }

    public function otherViewAction()
    {
        return $this->renderView(
            array('variable' => 'test'),
            'test/array.twig'
        );
    }
}
