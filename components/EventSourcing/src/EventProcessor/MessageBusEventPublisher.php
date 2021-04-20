<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;

/**
 * Implementation of an {@link EventPublisherInterface} that sends the events to a specific message bus.
 */
class MessageBusEventPublisher implements EventPublisherInterface
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var MessageNormalizerInterface
     */
    private $messageNormalizer;

    public function __construct(MessageBusInterface $messageBus, MessageNormalizerInterface $messageNormalizer)
    {
        $this->messageBus = $messageBus;
        $this->messageNormalizer = $messageNormalizer;
    }

    public function publishEvent(RecordedEventDescriptor $eventDescriptor): void
    {
        $eventData = $eventDescriptor->getEventData();
        $eventType = $eventDescriptor->getEventType();

        $event = $this->messageNormalizer->denormalize($eventData->toArray(), $eventType);

        $eventMetadata = $eventDescriptor->getEventMetadata()->toArray();
        $headersData = [
            MessageHeaders::MESSAGE_ID => (string) $eventDescriptor->getEventId(),
            MessageHeaders::CORRELATION_ID => $eventMetadata['correlationId'] ?? null,
            MessageHeaders::CAUSATION_ID => $eventMetadata['causationId'] ?? null,
            MessageHeaders::TENANT_ID => $eventMetadata['tenantId'] ?? null,
        ] + $eventMetadata;

        // TODO Schedule retry for failed event processors (?)
        $this->messageBus->sendMessage($event, new MessageHeaders($headersData));
    }
}
