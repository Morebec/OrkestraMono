<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\UnregisterService;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\MessageTypeName;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceId;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceMetadata;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceUrl;

class ServiceUnregisteredEvent implements DomainEventInterface
{
    /** @var string */
    public $serviceId;

    /** @var string */
    public $url;

    /** @var string[] */
    public $handledMessages;

    /** @var string */
    public $description;

    /** @var array */
    public $metadata;

    /** @var string */
    public $name;

    public function __construct(ServiceId $serviceId, ServiceUrl $url, string $name, string $description, array $handledMessages, ServiceMetadata $metadata)
    {
        $this->serviceId = (string) $serviceId;
        $this->url = (string) $url;
        $this->handledMessages = array_map(static function (MessageTypeName $m) {
            return (string) $m;
        }, $handledMessages);
        $this->description = $description;
        $this->metadata = $metadata->toArray();
        $this->name = $name;
    }

    public static function getTypeName(): string
    {
        return 'service.unregistered';
    }
}
