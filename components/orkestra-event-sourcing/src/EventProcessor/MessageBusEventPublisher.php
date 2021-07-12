<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationException;
use Throwable;

/**
 * Implementation of an {@link EventPublisherInterface} that sends the events to a specific message bus.
 */
class MessageBusEventPublisher implements EventPublisherInterface
{
    private MessageBusInterface $messageBus;

    private MessageNormalizerInterface $messageNormalizer;

    private bool $throwExceptions;

    public function __construct(
        MessageBusInterface $messageBus,
        MessageNormalizerInterface $messageNormalizer,
        bool $throwExceptions = true
    ) {
        $this->messageBus = $messageBus;
        $this->messageNormalizer = $messageNormalizer;
        $this->throwExceptions = $throwExceptions;
    }

    /**
     * @throws Throwable
     */
    public function publishEvent(RecordedEventDescriptor $eventDescriptor): void
    {
        $eventData = $eventDescriptor->getEventData();
        $eventType = $eventDescriptor->getEventType();

        $event = $this->messageNormalizer->denormalize($eventData->toArray(), $eventType);

        if (!$event) {
            throw new DenormalizationException("Could not denormalize event {id: {$eventDescriptor->getEventId()}, type: {$eventDescriptor->getEventType()}}, null received after denormalization.");
        }

        $eventMetadata = $eventDescriptor->getEventMetadata()->toArray();
        $headersData = [
            MessageHeaders::MESSAGE_ID => (string) $eventDescriptor->getEventId(),
            MessageHeaders::CORRELATION_ID => $eventMetadata['correlationId'] ?? null,
            MessageHeaders::CAUSATION_ID => $eventMetadata['causationId'] ?? null,
            MessageHeaders::TENANT_ID => $eventMetadata['tenantId'] ?? null,
        ] + $eventMetadata;

        $response = $this->messageBus->sendMessage($event, new MessageHeaders($headersData));

        if ($this->throwExceptions && $response->isFailure()) {
            throw $response->getPayload();
        }
    }
}
