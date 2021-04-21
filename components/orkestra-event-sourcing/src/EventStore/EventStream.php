<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Default implementation of an Event stream as an in-memory data structure.
 */
class EventStream implements EventStreamInterface
{
    /**
     * @var EventStreamId
     */
    private $id;

    /**
     * @var EventStreamVersion
     */
    private $version;

    public function __construct(EventStreamId $streamId, EventStreamVersion $streamVersion)
    {
        $this->id = $streamId;
        $this->version = $streamVersion;
    }

    public function getId(): EventStreamId
    {
        return $this->id;
    }

    public function getVersion(): EventStreamVersion
    {
        return $this->version;
    }
}
