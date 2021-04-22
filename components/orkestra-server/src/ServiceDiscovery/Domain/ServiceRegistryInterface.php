<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain;

/**
 * Used to persist information about services.
 */
interface ServiceRegistryInterface
{
    public function add(Service $service): void;

    public function update(Service $service): void;

    public function remove(Service $service): void;

    /**
     * @throws ServiceNotFoundException
     */
    public function findById(ServiceId $serviceId): Service;
}
