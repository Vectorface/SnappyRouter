<?php

namespace Vectorface\SnappyRouterTests\Controller;

use Vectorface\SnappyRouter\Controller\AbstractController;

class TestDummyController extends AbstractController
{
    public function testAction()
    {
        return array('response' => 'This is a dummy service.');
    }
}