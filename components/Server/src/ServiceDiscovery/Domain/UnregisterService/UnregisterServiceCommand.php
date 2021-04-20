<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\UnregisterService;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandInterface;

/**
 * Unregisters a service.
 */
class UnregisterServiceCommand implements DomainCommandInterface
{
    /** @var string */
    public $serviceId;

    public static function getTypeName(): string
    {
        return 'service.unregister';
    }
}
