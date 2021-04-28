<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamOptions;

/**
 * This service inspects an event processor for its current progress.
 */
class TrackingEventProcessorInspector
{
    public function __construct()
    {
    }

    /**
     * Inspects a Tracking Event processor's progress.
     */
    public function inspect(TrackingEventProcessor $processor): TrackingEventProcessorProgress
    {
        $streamId = $processor->getStreamId();
        $eventStore = $processor->getEventStore();

        if ($streamId->isEqualTo($eventStore->getGlobalStreamId())) {
            $firstEvent = $eventStore->readStream($streamId, ReadStreamOptions::firstEvent())->getFirst();
            $firstPosition = $firstEvent ? $firstEvent->getSequenceNumber()->toInt() : -1;

            $lastEvent = $eventStore->readStream($streamId, ReadStreamOptions::lastEvent())->getLast();
            $lastPosition = $lastEvent ? $lastEvent->getSequenceNumber()->toInt() : -1;
        } else {
            $stream = $eventStore->getStream($streamId);
            $streamVersion = $stream ? $stream->getVersion()->toInt() : 0;
            $firstPosition = $streamVersion === EventStreamVersion::INITIAL_VERSION ? EventStreamVersion::INITIAL_VERSION : $streamVersion;
            $lastPosition = $streamVersion;
        }

        $currentPosition = $processor->getPosition();

        return new TrackingEventProcessorProgress(
            $processor->getName(),
            $streamId,
            $firstPosition,
            $lastPosition,
            $currentPosition,
        );
    }
}
