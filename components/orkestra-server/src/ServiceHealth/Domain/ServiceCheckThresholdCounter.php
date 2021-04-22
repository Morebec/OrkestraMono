<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain;

use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckId;

/**
 * Service Check Threshold counters are used to track the health checks of a given service check on a specific service
 * in order to ensure that the threshold rules are applied before changing the status of a Service Check.
 */
class ServiceCheckThresholdCounter
{
    /**
     * @var ServiceId
     */
    private $serviceId;

    /**
     * @var ServiceCheckId
     */
    private $serviceCheckId;

    /**
     * Represents the next potential status.
     *
     * @var HealthStatus
     */
    private $healthStatus;

    /**
     * Represents the number of consecutive health checks that had this status so far.
     *
     * @var int
     */
    private $healthCheckCount;

    /**
     * ServiceCheckThresholdCounter constructor.
     */
    public function __construct(ServiceId $serviceId, ServiceCheckId $serviceCheckId, HealthStatus $potentialStatus, int $healthCheckCount)
    {
        $this->serviceId = $serviceId;
        $this->serviceCheckId = $serviceCheckId;
        $this->healthStatus = $potentialStatus;
        $this->healthCheckCount = $healthCheckCount;
    }

    /**
     * Records a new occurrence of a health check with a given status.
     */
    public function recordHealthCheckStatus(HealthStatus $status): void
    {
        if (!$this->healthStatus->isEqualTo($status)) {
            // Reset
            $this->healthStatus = $status;
            $this->healthCheckCount = 0;
        }

        $this->healthCheckCount++;
    }

    public function getHealthStatus(): HealthStatus
    {
        return $this->healthStatus;
    }

    public function getHealthCheckCount(): int
    {
        return $this->healthCheckCount;
    }

    public function getServiceId(): ServiceId
    {
        return $this->serviceId;
    }

    public function getServiceCheckId(): ServiceCheckId
    {
        return $this->serviceCheckId;
    }
}
