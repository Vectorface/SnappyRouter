<?php /** @noinspection PhpIllegalPsrClassPathInspection */

// @codingStandardsIgnoreStart
use Vectorface\SnappyRouter\Controller\AbstractController;

class NonNamespacedController extends AbstractController
{
    // @codingStandardsIgnoreEnd

    public function testAction()
    {
        return 'This is a test string.';
    }
}
