<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Precondition;

use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\ConcurrencyException;
use Morebec\Orkestra\EventSourcing\EventStore\DuplicateEventIdException;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptorInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;

class EventDescriptorInEventStorePrecondition implements TestStagePreconditionInterface
{
    private EventStoreInterface $eventStore;
    private EventDescriptorInterface $eventDescriptor;
    private ?AppendStreamOptions $appendStreamOptions;
    private EventStreamId $streamId;
    private MessageBusInterface $messageBus;
    private MessageNormalizerInterface $messageNormalizer;

    public function __construct(
        EventStoreInterface $eventStore,
        MessageBusInterface $messageBus,
        MessageNormalizerInterface $messageNormalizer,
        EventDescriptorInterface $eventDescriptor,
        EventStreamId $streamId,
        ?AppendStreamOptions $appendStreamOptions = null
    ) {
        $this->eventStore = $eventStore;
        $this->eventDescriptor = $eventDescriptor;
        $this->streamId = $streamId;
        $this->appendStreamOptions = $appendStreamOptions ?? AppendStreamOptions::append()->disableOptimisticConcurrencyCheck();
        $this->messageBus = $messageBus;
        $this->messageNormalizer = $messageNormalizer;
    }

    public function withOptions(AppendStreamOptions $options): self
    {
        $this->appendStreamOptions = $options;

        return $this;
    }

    /**
     * @throws ConcurrencyException
     * @throws DuplicateEventIdException
     */
    public function run(): void
    {
        $this->eventStore->appendToStream(
            $this->streamId,
            [$this->eventDescriptor],
            $this->appendStreamOptions
        );
        $e = $this->messageNormalizer->denormalize($this->eventDescriptor->getEventData()->toArray(), $this->eventDescriptor->getEventType());
        $this->messageBus->sendMessage($e, new MessageHeaders($this->eventDescriptor->getEventMetadata()->toArray()));
    }
}
