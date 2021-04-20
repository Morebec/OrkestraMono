<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\DisableHealthChecking;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandInterface;

class DisableServiceHealthCheckingCommand implements DomainCommandInterface
{
    /** @var string */
    public $serviceId;

    public function __construct(string $serviceId)
    {
        $this->serviceId = $serviceId;
    }

    public static function getTypeName(): string
    {
        return 'service.health_checking.disable';
    }
}
