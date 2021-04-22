<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck;

use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheck;

interface HealthCheckRunnerInterface
{
    public function runCheck(HealthCheck $healthCheck, ServiceCheck $serviceCheck): HealthCheckResponse;
}
