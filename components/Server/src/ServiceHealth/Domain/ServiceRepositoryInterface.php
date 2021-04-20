<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain;

interface ServiceRepositoryInterface
{
    public function add(Service $service): void;

    public function update(Service $service): void;

    /**
     * @throws ServiceNotFoundException
     */
    public function findById(ServiceId $serviceId): Service;
}
