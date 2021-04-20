<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain;

use Morebec\Orkestra\EventSourcing\Modeling\AbstractEventSourcedAggregateRoot;
use Morebec\Orkestra\EventSourcing\Modeling\EventSourcedAggregateRootTrait;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\BlacklistService\ServiceBlacklistedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\BlacklistService\ServiceBlacklistedException;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\RegisterService\ServiceRegisteredEvent;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\UnregisterService\ServiceUnregisteredEvent;

class Service extends AbstractEventSourcedAggregateRoot
{
    use EventSourcedAggregateRootTrait;

    /**
     * @var ServiceId
     */
    private $id;

    /** @var string */
    private $name;

    /** @var string|null */
    private $description;

    /** @var ServiceUrl */
    private $url;

    /**
     * @var MessageTypeName[]
     */
    private $handledMessages;

    /**
     * @var ServiceMetadata
     */
    private $metadata;

    /** @var bool */
    private $blacklisted;

    /** @var bool */
    private $registered;

    public static function initialize(ServiceId $serviceId): self
    {
        $s = new self();

        $s->recordDomainEvent(new ServiceInitializedEvent($serviceId));

        return $s;
    }

    public function getId(): ServiceId
    {
        return $this->id;
    }

    /**
     * Registers service updating its information if any.
     *
     * @param MessageTypeName[] $handledMessages
     */
    public function register(ServiceUrl $url, array $handledMessages, ?string $name, ?string $description, ServiceMetadata $metadata)
    {
        if (!$name) {
            $name = $this->id;
        }

        if ($description === '') {
            $description = null;
        }

        $this->recordDomainEvent(
            new ServiceRegisteredEvent(
            $this->id,
            $url,
            $name,
            $description,
            $handledMessages,
            $metadata,
            $this->isRegistered()
        )
        );
    }

    /**
     * Unregisters the service.
     */
    public function unregister(): void
    {
        if (!$this->isRegistered()) {
            return;
        }

        $this->recordDomainEvent(
            new ServiceUnregisteredEvent(
            $this->id,
            $this->url,
            $this->name,
            $this->description,
            $this->handledMessages,
            $this->metadata
        )
        );
    }

    /**
     * Blacklists a service.
     */
    public function blacklist(): void
    {
        if ($this->blacklisted) {
            return;
        }

        $this->recordDomainEvent(new ServiceBlacklistedEvent($this->id));
        $this->unregister();
    }

    public function recordDomainEvent(DomainEventInterface $event): void
    {
        if ($this->blacklisted) {
            throw new ServiceBlacklistedException($this->id);
        }

        parent::recordDomainEvent($event);
    }

    public function applyServiceInitialized(ServiceInitializedEvent $event): void
    {
        $this->id = ServiceId::fromString($event->serviceId);
        $this->handledMessages = [];
        $this->description = null;
        $this->name = $this->id;
        $this->registered = false;
    }

    public function applyServiceRegistered(ServiceRegisteredEvent $event): void
    {
        $this->name = $event->name;
        $this->url = ServiceUrl::fromString($event->url);
        $this->handledMessages = array_map(static function (string $message) {
            return MessageTypeName::fromString($message);
        }, $event->handledMessages);
        $this->description = $event->description;
        $this->metadata = ServiceMetadata::fromArray($event->metadata);
        $this->registered = true;
    }

    public function applyServiceUnregistered(ServiceUnregisteredEvent $event): void
    {
        $this->registered = false;
    }

    public function applyServiceBlacklistedEvent(ServiceBlacklistedEvent $event): void
    {
        $this->blacklisted = true;
    }

    private function isRegistered(): bool
    {
        return $this->registered;
    }
}
