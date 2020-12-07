<?php

namespace Vectorface\SnappyRouterTests\Task;

use Exception;
use Vectorface\SnappyRouter\Task\AbstractTask;

class DummyTestTask extends AbstractTask
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function testMethod()
    {
        $options = $this->getOptions();
        $this->set('taskOptions', $options);
        $this->set('response', 'Hello World');
        return $this->get('response');
    }

    /**
     * @throws Exception
     */
    public function throwsException()
    {
        throw new Exception('An exception was thrown.');
    }
}
