<?php

namespace Vectorface\SnappyRouterTests\Di;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Di\ServiceProvider;

/**
 * Tests the ServiceProvider class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class ServiceProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * An overview of how to use the ServiceProvider class.
     * @test
     */
    public function synopsis()
    {
        // instantiate the class
        $config = array(
            'TestController' => 'Vectorface\SnappyRouterTests\Controller\TestDummyController'
        );
        $serviceProvider = new ServiceProvider($config);

        // public setters (object chaining)
        $services = array_merge(
            $config,
            array('AnotherService' => '/path/to/anotherService.php')
        );
        $serviceProvider->setService('AnotherService', '/path/to/anotherService.php');

        // public getters
        $this->assertEquals(
            array_keys($services),
            $serviceProvider->getServices()
        );
        $this->assertEquals(
            '/path/to/anotherService.php',
            $serviceProvider->getService('AnotherService')
        );

        $this->assertInstanceOf(
            'Vectorface\SnappyRouterTests\Controller\TestDummyController',
            $serviceProvider->getServiceInstance('TestController')
        );
        $this->assertInstanceOf(
            'Vectorface\SnappyRouterTests\Controller\TestDummyController',
            $serviceProvider->getServiceInstance('TestController')
        );
    }

    /**
     * Test that we can retrieve a non namespaced service.
     */
    public function testNonNamespacedService()
    {
        $config = array(
            'NonNamespacedController' => 'tests/Vectorface/SnappyRouterTests/Controller/NonNamespacedController.php'
        );
        $serviceProvider = new ServiceProvider($config);

        $this->assertInstanceOf(
            'NonNamespacedController',
            $serviceProvider->getServiceInstance('NonNamespacedController')
        );
    }
}
