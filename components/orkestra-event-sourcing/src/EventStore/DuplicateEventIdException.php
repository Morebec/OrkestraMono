<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use Throwable;

/**
 * When appending, thrown if an event with a given Id was already found in the given stream.
 */
class DuplicateEventIdException extends \RuntimeException implements EventStoreExceptionInterface
{
    /**
     * @var EventStreamId
     */
    private $eventStreamId;
    /**
     * @var EventId
     */
    private $eventId;

    public function __construct(EventStreamId $eventStreamId, EventId $eventId, Throwable $previous = null)
    {
        parent::__construct(sprintf('Event0 "%s" was already in stream "%s".', $eventId, $eventStreamId), 0, $previous);
        $this->eventStreamId = $eventStreamId;
        $this->eventId = $eventId;
    }

    public function getEventStreamId(): EventStreamId
    {
        return $this->eventStreamId;
    }

    public function getEventId(): EventId
    {
        return $this->eventId;
    }
}
