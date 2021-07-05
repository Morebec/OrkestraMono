<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;

/**
 * Read only data structure representing the current tracking status of a Tracking Event Processor.
 */
class TrackingEventProcessorProgress
{
    private string $eventProcessorName;

    private EventStreamId $streamId;

    private int $firstPosition;

    private int $lastPosition;

    private int $currentPosition;

    /**
     * TrackingEventProcessorStatus constructor.
     */
    public function __construct(
        string $eventProcessorName,
        EventStreamId $streamId,
        int $firstPosition,
        int $lastPosition,
        int $currentPosition
    ) {
        $this->eventProcessorName = $eventProcessorName;
        $this->streamId = $streamId;
        $this->firstPosition = $firstPosition;
        $this->lastPosition = $lastPosition;
        $this->currentPosition = $currentPosition;
    }

    public function getStreamId(): EventStreamId
    {
        return $this->streamId;
    }

    public function getCurrentPosition(): int
    {
        return $this->currentPosition;
    }

    /**
     * Returns the name of the event processor.
     */
    public function getEventProcessorName(): string
    {
        return $this->eventProcessorName;
    }

    /**
     * Returns the first position of the stream as an int.
     */
    public function getFirstPosition(): int
    {
        return $this->firstPosition;
    }

    /**
     * Returns the last position of the stream as an int.
     */
    public function getLastPosition(): int
    {
        return $this->lastPosition;
    }

    /**
     * Returns the number of event that were processed.
     */
    public function getNumberEventProcessed(): int
    {
        if ($this->currentPosition === EventStreamVersion::INITIAL_VERSION) {
            return 0;
        }

        // We do a + 1 here because the initial event must be included
        // 0 1 [2] 3 -> where [2] is current position we must include 0-1-2 which amounts to three since the first element counts.
        return $this->currentPosition - $this->firstPosition + 1;
    }

    /**
     * Returns the number of events that needs processing.
     */
    public function getNumberEventsToProcess(): int
    {
        return $this->getTotalNumberEvents() - $this->getNumberEventProcessed();
    }

    /**
     * Returns the total number of events in the stream.
     */
    public function getTotalNumberEvents(): int
    {
        // Empty stream
        if ($this->lastPosition === EventStreamVersion::INITIAL_VERSION) {
            return 0;
        }

        // We do a +1 here because the initial event must be included
        // 0 1 2 -> 3 events
        // 2 3 4 5 -> 4 events
        return $this->lastPosition - $this->firstPosition + 1;
    }

    /**
     * Returns a number out of 100 representing the processing progress the processor has made.
     */
    public function getProgressPercentage(): float
    {
        $totalNumberEvents = $this->getTotalNumberEvents();

        return round($this->getNumberEventProcessed() / ($totalNumberEvents === 0 ? 1 : $totalNumberEvents) * 100, 2);
    }
}
