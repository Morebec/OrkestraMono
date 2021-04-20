<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\RegisterService;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandInterface;

/**
 * Registers a service or updates its registration.
 */
class RegisterServiceCommand implements DomainCommandInterface
{
    /** @var string */
    public $serviceId;

    /** @var string */
    public $url;

    /** @var string[] (optional) */
    public $handledMessages;

    /** @var string|null (optional) */
    public $name;

    /** @var string|null (optional) */
    public $description;

    /** @var array (optional) */
    public $metadata;

    public static function getTypeName(): string
    {
        return 'service.register';
    }
}
