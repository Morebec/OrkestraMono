<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandInterface;

class RunHealthCheckCommand implements DomainCommandInterface
{
    /** @var string */
    public $serviceId;

    /** @var string */
    public $healthCheckId;

    public function __construct(string $serviceId, string $healthCheckId)
    {
        $this->serviceId = $serviceId;
        $this->healthCheckId = $healthCheckId;
    }

    public static function getTypeName(): string
    {
        return 'service.health_check.run';
    }
}
