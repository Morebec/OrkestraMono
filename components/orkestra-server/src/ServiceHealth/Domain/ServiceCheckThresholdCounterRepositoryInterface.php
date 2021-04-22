<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain;

use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckId;

interface ServiceCheckThresholdCounterRepositoryInterface
{
    public function add(ServiceCheckThresholdCounter $counter): void;

    public function update(ServiceCheckThresholdCounter $counter): void;

    public function remove(ServiceId $serviceId, ServiceCheckId $serviceCheckId): void;

    public function find(ServiceId $serviceId, ServiceCheckId $serviceCheckId): ServiceCheckThresholdCounter;
}
