<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\BlacklistService;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandInterface;

class BlacklistServiceCommand implements DomainCommandInterface
{
    /** @var string */
    public $serviceId;

    public static function getTypeName(): string
    {
        return 'service.block';
    }
}
