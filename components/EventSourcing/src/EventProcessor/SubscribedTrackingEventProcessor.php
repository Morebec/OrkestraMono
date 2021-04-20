<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreSubscriberInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\SubscriptionOptions;

/**
 * Implementation of a {@link TrackingEventProcessor} that is also registered as a subscriber on the event
 * store so that it can perform its work only on realtime events. It automatically subscribes to the event store
 * upon construction.
 */
class SubscribedTrackingEventProcessor extends TrackingEventProcessor implements EventStoreSubscriberInterface
{
    public function __construct(
        EventPublisherInterface $publisher,
        EventStoreInterface $eventStore,
        EventStorePositionStorageInterface $storage,
        SubscribedTrackingEventProcessorOptions $options
    ) {
        parent::__construct($publisher, $eventStore, $storage, $options);
        $this->eventStore->subscribeToStream(EventStreamId::fromString($options->streamId), $this);
    }

    public function onEvent(EventStoreInterface $eventStore, RecordedEventDescriptor $eventDescriptor): void
    {
        $this->start();
    }

    public function stop(): void
    {
        // TODO Find a way to unsubscribe from the event store.
        parent::stop();
    }

    public function getOptions(): SubscriptionOptions
    {
        return SubscriptionOptions::subscribe()->fromEnd();
    }
}
