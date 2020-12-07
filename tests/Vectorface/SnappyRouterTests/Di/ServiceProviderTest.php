<?php

namespace Vectorface\SnappyRouterTests\Di;

use Exception;
use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Di\ServiceProvider;
use Vectorface\SnappyRouterTests\Controller\TestDummyController;

/**
 * Tests the ServiceProvider class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class ServiceProviderTest extends TestCase
{
    /**
     * An overview of how to use the ServiceProvider class.
     *
     * @throws Exception
     */
    public function testSynopsis()
    {
        // instantiate the class
        $config = [
            'TestController' => TestDummyController::class
        ];
        $serviceProvider = new ServiceProvider($config);

        // public setters (object chaining)
        $services = array_merge(
            $config,
            [
                'AnotherService'             => '/path/to/anotherService.php',
                'AnotherServiceForFileClass' => null
            ]
        );

        $serviceProvider->setService('AnotherService', '/path/to/anotherService.php');
        $serviceProvider->setService(
            'AnotherServiceForFileClass',
            [
                'file'  => 'tests/Vectorface/SnappyRouterTests/Controller/NonNamespacedController.php',
                'class' => 'NonNamespacedController',
            ]
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
            TestDummyController::class,
            $serviceProvider->getServiceInstance('TestController')
        );

        //Tests instanceCache
        $this->assertInstanceOf(
            TestDummyController::class,
            $serviceProvider->getServiceInstance('TestController')
        );

        $this->assertInstanceOf(
            'NonNamespacedController',
            $serviceProvider->getServiceInstance('AnotherServiceForFileClass')
        );
    }

    /**
     * Test that we can retrieve a non namespaced service.
     *
     * @throws Exception
     */
    public function testNonNamespacedService()
    {
        $config = [
            'NonNamespacedController' => 'tests/Vectorface/SnappyRouterTests/Controller/NonNamespacedController.php'
        ];
        $serviceProvider = new ServiceProvider($config);

        $this->assertInstanceOf(
            'NonNamespacedController',
            $serviceProvider->getServiceInstance('NonNamespacedController')
        );
    }

    /**
     * Test that we can retrieve a service while in namespace provisioning mode.
     *
     * @throws Exception
     */
    public function testNamespaceProvisioning()
    {
        $serviceProvider = new ServiceProvider([]);
        $namespaces = ['Vectorface\SnappyRouterTests\Controller'];
        $serviceProvider->setNamespaces($namespaces);

        $this->assertInstanceOf(
            TestDummyController::class,
            $serviceProvider->getServiceInstance('TestDummyController')
        );
    }

    /**
     * Test that we get an exception if we cannot find the service in any
     * of the given namespaces.
     *
     * @throws Exception
     */
    public function testNamespaceProvisioningMissingService()
    {
        $this->setExpectedException(Exception::class, "Controller class TestDummyController was not found in any listed namespace.");

        $serviceProvider = new ServiceProvider([]);
        $serviceProvider->setNamespaces([]);

        $this->assertInstanceOf(
            TestDummyController::class,
            $serviceProvider->getServiceInstance('TestDummyController')
        );
    }

    /**
     * Test that we can retrieve a service while in folder provisioning mode.
     *
     * @throws Exception
     */
    public function testFolderProvisioning()
    {
        $serviceProvider = new ServiceProvider([]);
        $folders = [realpath(__DIR__.'/../')];
        $serviceProvider->setFolders($folders);

        $this->assertInstanceOf(
            'NonNamespacedController',
            $serviceProvider->getServiceInstance('NonNamespacedController')
        );
    }

    /**
     * Test that we get an exception if we cannot find the service in any
     * of the given folders (recursively checking).
     *
     * @throws Exception
     */
    public function testFolderProvisioningMissingService()
    {
        $this->setExpectedException(Exception::class, "Controller class NonExistentController not found in any listed folder.");

        $serviceProvider = new ServiceProvider([]);
        $folders = [realpath(__DIR__.'/../')];
        $serviceProvider->setFolders($folders);

        $serviceProvider->getServiceInstance('NonExistentController');
    }
}
