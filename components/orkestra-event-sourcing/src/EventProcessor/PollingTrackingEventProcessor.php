<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;

/**
 * Implementation of a {@link TrackingEventProcessor} that continuously polls the event store for new events.
 * Therefore once it has processed a batch of events, it polls the database for new events in an infinite loop
 * or until it has been called to stop.
 */
class PollingTrackingEventProcessor extends TrackingEventProcessor
{
    public function __construct(
        EventPublisherInterface $publisher,
        EventStoreInterface $eventStore,
        EventStorePositionStorageInterface $storage,
        PollingTrackingEventProcessorOptions $options
    ) {
        parent::__construct($publisher, $eventStore, $storage, $options);
    }

    public function start(): void
    {
        /** @var PollingTrackingEventProcessorOptions $options */
        $options = $this->options;
        do {
            parent::start();
            usleep($options->pollingDelay * 1000);
        } while ($this->isRunning());
    }
}
