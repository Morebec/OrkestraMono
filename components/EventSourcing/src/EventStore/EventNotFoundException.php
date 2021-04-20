<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use Throwable;

/**
 * Thrown when an event with a given ID was expected to be found.
 */
class EventNotFoundException extends \RuntimeException implements EventStoreExceptionInterface
{
    public function __construct(EventId $eventId, Throwable $previous = null)
    {
        parent::__construct(sprintf('Event "%s" not found.', $eventId), 0, $previous);
    }
}
