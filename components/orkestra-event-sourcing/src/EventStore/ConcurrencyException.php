<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use Throwable;

/**
 * Thrown when there is a concurrency issue while trying to append events to a stream.
 */
class ConcurrencyException extends \RuntimeException implements EventStoreExceptionInterface
{
    /**
     * @var EventStreamId
     */
    private $streamId;

    /**
     * @var EventStreamVersion
     */
    private $expectedStreamVersion;

    /**
     * @var EventStreamVersion
     */
    private $actualStreamVersion;

    public function __construct(
        EventStreamId $streamId,
        EventStreamVersion $expectedStreamVersion,
        EventStreamVersion $actualStreamVersion,
        $code = 0,
        Throwable $previous = null
    ) {
        $message = sprintf(
            'Event Store: Concurrency issue encountered on stream with "%s", expected version: "%s", actual version: "%s".',
            $streamId,
            $expectedStreamVersion->toInt(),
            $actualStreamVersion->toInt()
        );
        parent::__construct($message, $code, $previous);
        $this->streamId = $streamId;
        $this->expectedStreamVersion = $expectedStreamVersion;
        $this->actualStreamVersion = $actualStreamVersion;
    }

    public function getStreamId(): EventStreamId
    {
        return $this->streamId;
    }

    public function getExpectedStreamVersion(): EventStreamVersion
    {
        return $this->expectedStreamVersion;
    }

    public function getActualStreamVersion(): EventStreamVersion
    {
        return $this->actualStreamVersion;
    }
}
