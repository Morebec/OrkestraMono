<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\Messaging\Context\MessageBusContext;
use Morebec\Orkestra\Messaging\Context\MessageBusContextProvider;
use Morebec\Orkestra\Messaging\MessageHeaders;

/**
 * Decorator of an Event Store that adds the headers of the Message Bus Context,
 * to the events before appending them.
 * By default, this implementation adds the following headers:
 * - the correlation ID
 * - the causation ID
 * - the tenant ID
 * - the user ID
 * - the application ID
 * It can be extended to add more data.
 */
class MessageBusContextEventStoreDecorator implements EventStoreInterface
{
    /**
     * @var EventStoreInterface
     */
    private $eventStore;
    /**
     * @var MessageBusContextProvider
     */
    private $contextProvider;

    public function __construct(
        EventStoreInterface $eventStore,
        MessageBusContextProvider $contextProvider
    ) {
        $this->eventStore = $eventStore;
        $this->contextProvider = $contextProvider;
    }

    public function getGlobalStreamId(): EventStreamId
    {
        return $this->eventStore->getGlobalStreamId();
    }

    public function appendToStream(EventStreamId $streamId, iterable $eventDescriptors, AppendStreamOptions $options): void
    {
        $context = $this->contextProvider->getContext();

        if ($context) {
            $updated = [];
            /** @var EventDescriptorInterface $eventDescriptor */
            foreach ($eventDescriptors as $eventDescriptor) {
                $updated[] = new EventDescriptor(
                    $eventDescriptor->getEventId(),
                    $eventDescriptor->getEventType(),
                    $eventDescriptor->getEventData(),
                    $this->processMetadata($eventDescriptor, $context)
                );
            }

            $eventDescriptors = $updated;
        }

        $this->eventStore->appendToStream($streamId, $eventDescriptors, $options);
    }

    public function readStream(EventStreamId $streamId, ReadStreamOptions $options): StreamedEventCollectionInterface
    {
        return $this->eventStore->readStream($streamId, $options);
    }

    public function getStream(EventStreamId $streamId): ?EventStreamInterface
    {
        return $this->eventStore->getStream($streamId);
    }

    public function streamExists(EventStreamId $streamId): bool
    {
        return $this->eventStore->streamExists($streamId);
    }

    public function subscribeToStream(EventStreamId $streamId, EventStoreSubscriberInterface $subscriber): void
    {
        $this->eventStore->subscribeToStream($streamId, $subscriber);
    }

    private function processMetadata(EventDescriptorInterface $eventDescriptor, MessageBusContext $context): MutableEventMetadata
    {
        $headers = $context->getMessageHeaders();

        $metadata = new MutableEventMetadata($eventDescriptor->getEventMetadata()->toArray());

        $metadata->putValue('correlationId', $headers->get(MessageHeaders::CORRELATION_ID));
        $metadata->putValue('causationId', $context->getMessageId());
        $metadata->putValue('applicationId', $headers->get(MessageHeaders::APPLICATION_ID));
        $metadata->putValue('userId', $headers->get(MessageHeaders::USER_ID));
        $metadata->putValue('tenantId', $headers->get(MessageHeaders::TENANT_ID));

        return $metadata;
    }
}
