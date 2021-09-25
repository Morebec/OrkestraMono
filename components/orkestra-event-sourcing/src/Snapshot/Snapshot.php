<?php

namespace Morebec\Orkestra\EventSourcing\Snapshot;

use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;

class Snapshot
{
    private array $state;

    private EventStreamVersion $streamVersion;

    private EventSequenceNumber $sequenceNumber;

    private EventStreamId $streamId;

    public function __construct(
        EventStreamId $streamId,
        EventStreamVersion $streamVersion,
        EventSequenceNumber $sequenceNumber,
        array $state
    ) {
        $this->state = $state;
        $this->streamVersion = $streamVersion;
        $this->sequenceNumber = $sequenceNumber;
        $this->streamId = $streamId;
    }

    public function getState(): array
    {
        return $this->state;
    }

    public function getSequenceNumber(): EventSequenceNumber
    {
        return $this->sequenceNumber;
    }

    public function getStreamVersion(): EventStreamVersion
    {
        return $this->streamVersion;
    }

    public function getEventStreamId(): EventStreamId
    {
        return $this->streamId;
    }
}
