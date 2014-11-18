<?php

namespace Vectorface\SnappyRouterTests\Task;

use \Exception;
use Vectorface\SnappyRouter\Task\AbstractTask;

class DummyTestTask extends AbstractTask
{
    public function testMethod()
    {
        $this->getOptions();
        $this->set('response', 'Hello World');
        return $this->get('response');
    }

    public function throwsException()
    {
        throw new Exception('An exception was thrown.');
    }
}
