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
            array(
                'AnotherService' => '/path/to/anotherService.php',
                'AnotherServiceForFileClass' => null
            )
        );

        $serviceProvider->setService('AnotherService', '/path/to/anotherService.php');
        $serviceProvider->setService(
            'AnotherServiceForFileClass',
            array(
                'file' => 'tests/Vectorface/SnappyRouterTests/Controller/NonNamespacedController.php',
                'class' => 'NonNamespacedController',
            )
        );

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

        //Tests instanceCache
        $this->assertInstanceOf(
            'Vectorface\SnappyRouterTests\Controller\TestDummyController',
            $serviceProvider->getServiceInstance('TestController')
        );

        $this->assertInstanceOf(
            'NonNamespacedController',
            $serviceProvider->getServiceInstance('AnotherServiceForFileClass')
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

    /**
     * Test that we can retrieve a service while in namespace provisioning mode.
     */
    public function testNamespaceProvisioning()
    {
        $serviceProvider = new ServiceProvider(array());
        $namespaces = array('Vectorface\SnappyRouterTests\Controller');
        $serviceProvider->setNamespaces($namespaces);

        $this->assertInstanceOf(
            'Vectorface\SnappyRouterTests\Controller\TestDummyController',
            $serviceProvider->getServiceInstance('TestDummyController')
        );
    }

    /**
     * Test that we get an exception if we cannot find the service in any
     * of the given namespaces.
     * @expectedException Exception
     * @expectedExceptionMessage Controller class TestDummyController was not found in any listed namespace.
     */
    public function testNamespaceProvisioningMissingService()
    {
        $serviceProvider = new ServiceProvider(array());
        $serviceProvider->setNamespaces(array());

        $this->assertInstanceOf(
            'Vectorface\SnappyRouterTests\Controller\TestDummyController',
            $serviceProvider->getServiceInstance('TestDummyController')
        );
    }

    /**
     * Test that we can retrieve a service while in folder provisioning mode.
     */
    public function testFolderProvisioning()
    {
        $serviceProvider = new ServiceProvider(array());
        $folders = array(realpath(__DIR__.'/../'));
        $serviceProvider->setFolders($folders);

        $this->assertInstanceOf(
            'NonNamespacedController',
            $serviceProvider->getServiceInstance('NonNamespacedController')
        );
    }

    /**
     * Test that we get an exception if we cannot find the service in any
     * of the given folders (recursively checking).
     * @expectedException Exception
     * @expectedExceptionMessage Controller class NonExistantController not found in any listed folder.
     */
    public function testFolderProvisioningMissingService()
    {
        $serviceProvider = new ServiceProvider(array());
        $folders = array(realpath(__DIR__.'/../'));
        $serviceProvider->setFolders($folders);

        $serviceProvider->getServiceInstance('NonExistantController');
    }
}
