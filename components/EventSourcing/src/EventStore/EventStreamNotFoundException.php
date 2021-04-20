<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use Throwable;

class EventStreamNotFoundException extends \RuntimeException implements EventStoreExceptionInterface
{
    /**
     * @var EventStreamId
     */
    private $streamId;

    public function __construct(EventStreamId $streamId, $code = 0, Throwable $previous = null)
    {
        $this->streamId = $streamId;
        $message = sprintf('Stream "%s" not found.', $streamId);
        parent::__construct($message, $code, $previous);
    }

    public function getStreamId(): EventStreamId
    {
        return $this->streamId;
    }
}
