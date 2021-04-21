<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use Throwable;

/**
 * Thrown when trying to append to a virtual stream.
 */
class CannotAppendToVirtualStreamException extends \LogicException implements EventStoreExceptionInterface
{
    public function __construct(EventStreamId $streamId, Throwable $previous = null)
    {
        parent::__construct(sprintf('Cannot append to the stream "%s" as it is a virtual stream.', $streamId), 0, $previous);
    }
}
