<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck;

interface HealthCheckRepositoryInterface
{
    public function add(HealthCheck $healthCheck): void;

    public function update(HealthCheck $healthCheck): void;

    public function findById(HealthCheckId $id): HealthCheck;
}
