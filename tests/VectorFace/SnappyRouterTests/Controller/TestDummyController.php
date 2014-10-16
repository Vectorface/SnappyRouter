<?php

namespace VectorFace\SnappyRouterTests\Controller;

use VectorFace\SnappyRouter\Controller\AbstractController;

class TestDummyController extends AbstractController
{
    public function testAction()
    {
        return array('response' => 'This is a dummy service.');
    }
}