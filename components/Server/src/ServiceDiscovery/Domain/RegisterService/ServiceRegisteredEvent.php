<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\RegisterService;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\MessageTypeName;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceId;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceMetadata;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceUrl;

class ServiceRegisteredEvent implements DomainEventInterface
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

    /**
     * Indicates if the service was registered prior to this registration or not.
     *
     * @var bool
     */
    public $wasAlreadyRegistered;

    public function __construct(
        ServiceId $serviceId,
        ServiceUrl $url,
        string $name,
        ?string $description,
        array $handledMessages,
        ServiceMetadata $metadata,
        bool $wasAlreadyRegistered
    ) {
        $this->name = $name;
        $this->serviceId = (string) $serviceId;
        $this->url = (string) $url;
        $this->handledMessages = array_map(static function (MessageTypeName $m) {
            return (string) $m;
        }, $handledMessages);
        $this->description = $description;
        $this->metadata = $metadata->toArray();
        $this->wasAlreadyRegistered = $wasAlreadyRegistered;
    }

    public static function getTypeName(): string
    {
        return 'service.registered';
    }
}
