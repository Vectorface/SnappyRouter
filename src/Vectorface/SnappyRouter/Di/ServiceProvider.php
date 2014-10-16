<?php

namespace Vectorface\SnappyRouter\Di;

use Vectorface\SnappyRouter\Exception\ServiceNotRegisteredException;

/**
 * A service provider providing dependency injection capabilities to the
 * router specifically for the list of services.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class ServiceProvider extends Di
{
    // a cache of service instances
    private $instanceCache;

    /**
     * Returns the array of all services.
     * @return The array of all services.
     */
    public function getServices()
    {
        return $this->allRegisteredElements();
    }

    /**
     * Returns the specified service path for the given key.
     * @param string $key The key to lookup.
     * @return Returns the path to the specified service for the given key.
     * @throws ServiceNotFoundForKeyException Throws this exception if the key isn't associated
     * with any registered service.
     */
    public function getService($key)
    {
        try {
            return $this->get($key);
        } catch (Exception $e) {
            throw new ServiceNotRegisteredException('No service was found for key: '.$key);
        }
    }

    /**
     * Specifies the mapping between the given key and service.
     * @param string $key The key to assign.
     * @param string $service The service to be assigned to the key.
     * @return Returns $this.
     */
    public function setService($key, $service)
    {
        $this->set($key, $service);
        unset($this->instanceCache[$key]);
        return $this;
    }

    /**
     * Returns an instance of the specified service.
     * @param string $key The key to lookup.
     * @param boolean $useCache An optional flag indicating whether we should
     *        use the cache. True by default.
     * @return Returns an instance of the specified service.
     * @throws ServiceNotFoundForKeyException Throws this exception if the key isn't associated
     * with any registered service.
     */
    public function getServiceInstance($key, $useCache = true)
    {
        if ($useCache && isset($this->instanceCache[$key])) {
            return $this->instanceCache[$key];
        }

        $serviceClass = null;
        try {
            $serviceClass = $this->get($key);
        } catch (Exception $e) {
            throw new ServiceNotRegisteredException('No service was found for key: '.$key);
        }

        if (class_exists($serviceClass)) {
            // the value must be a namespaced class so return a direct instance of it
            $instance = new $serviceClass();
        } else {
            // we attempt to include the file and assume the key is the class name
            @require_once $serviceClass;
            if (!class_exists($key)) {
                throw new ServiceNotRegisteredException('Cannot find object instance: '.$key);
            }
            $instance = new $key();
        }

        if ($useCache) {
            $this->instanceCache[$key] = $instance;
        }
        return $instance;
    }
}
