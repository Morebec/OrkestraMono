<?php

namespace Morebec\Orkestra\EventSourcing\Snapshot;

use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;

class Snapshot
{
    /**
     * @var array
     */
    private $state;

    /**
     * @var EventStreamVersion
     */
    private $streamVersion;

    /**
     * @var EventSequenceNumber
     */
    private $sequenceNumber;
    /**
     * @var EventStreamId
     */
    private $streamId;

    public function __construct(
        EventStreamId $streamId,
        EventStreamVersion $streamVersion,
        EventSequenceNumber $sequenceNumber,
        array $data
    ) {
        $this->state = $data;
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
