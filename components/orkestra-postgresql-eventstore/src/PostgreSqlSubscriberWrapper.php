<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreSubscriberInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\SubscriptionOptions;

/**
 * Wraps {@link EventStoreSubscriberInterface}.
 *
 * @internal
 */
class PostgreSqlSubscriberWrapper implements EventStoreSubscriberInterface
{
    private EventStoreSubscriberInterface $subscriber;

    private EventStreamId $streamId;

    public function __construct(EventStreamId $streamId, EventStoreSubscriberInterface $subscriber)
    {
        $this->subscriber = $subscriber;
        $this->streamId = $streamId;
    }

    public function onEvent(EventStoreInterface $eventStore, RecordedEventDescriptor $eventDescriptor): void
    {
        $this->subscriber->onEvent($eventStore, $eventDescriptor);
    }

    public function getOptions(): SubscriptionOptions
    {
        return $this->subscriber->getOptions();
    }

    public function getStreamId(): EventStreamId
    {
        return $this->streamId;
    }
}
