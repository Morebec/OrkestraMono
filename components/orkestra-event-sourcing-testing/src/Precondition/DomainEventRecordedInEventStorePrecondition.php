<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Precondition;

use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\ConcurrencyException;
use Morebec\Orkestra\EventSourcing\EventStore\DuplicateEventIdException;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;

/**
 * Precondition where certain events were added to the event store.
 */
class DomainEventRecordedInEventStorePrecondition implements TestStagePreconditionInterface
{
    private EventDescriptorInEventStorePrecondition $inner;

    public function __construct(
        MessageBusInterface $messageBus,
        MessageNormalizerInterface $normalizer,
        EventStoreInterface $eventStore,
        DomainEventInterface $event,
        EventStreamId $streamId,
        ?AppendStreamOptions $options = null
    ) {
        $eventDescriptor = new EventDescriptor(
            EventId::generate(),
            EventType::fromString($event::getTypeName()),
            new EventData($normalizer->normalize($event))
        );
        $this->inner = new EventDescriptorInEventStorePrecondition(
            $eventStore,
            $messageBus,
            $normalizer,
            $eventDescriptor,
            $streamId,
            $options ?? AppendStreamOptions::append()
        );
    }

    /**
     * @throws ConcurrencyException
     * @throws DuplicateEventIdException
     */
    public function run(): void
    {
        $this->inner->run();
    }
}
