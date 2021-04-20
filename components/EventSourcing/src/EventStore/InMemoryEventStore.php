<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\DateTime\ClockInterface;

/**
 * In Memory implementation of an event store.
 */
class InMemoryEventStore implements EventStoreInterface
{
    public const GLOBAL_EVENT_STREAM_ID = '$all';

    public const EVENT_STORE_IDENTIFIER = 'IN_MEMORY';

    public const EVENT_STORE_VERSION = '2.0';

    /**
     * @var RecordedEventDescriptor[]
     */
    private $events;

    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @var EventStoreSubscriberInterface[][]
     */
    private $subscribers = [];

    public function __construct(ClockInterface $clock)
    {
        $this->events = [];
        $this->subscribers = [];

        $this->clock = $clock;
    }

    public function getGlobalStreamId(): EventStreamId
    {
        return EventStreamId::fromString(self::GLOBAL_EVENT_STREAM_ID);
    }

    public function appendToStream(EventStreamId $streamId, iterable $eventDescriptors, AppendStreamOptions $options): void
    {
        // Make sure it is not a virtual stream.
        if ($streamId === $this->getGlobalStreamId()) {
            throw new CannotAppendToVirtualStreamException($streamId);
        }

        // Ensure all $eventDescriptors are instances of EventDescriptorInterface.
        foreach ($eventDescriptors as $descriptor) {
            if (!($descriptor instanceof EventDescriptorInterface)) {
                $expectedType = EventDescriptorInterface::class;
                $message = sprintf('Invalid argument, expected "%s", got "%s"', $expectedType, get_debug_type($descriptor));
                throw new \InvalidArgumentException($message);
            }
        }

        $stream = $this->getStream($streamId);
        $streamVersion = $stream ? $stream->getVersion() : EventStreamVersion::initial();

        // Check concurrency
        if ($options->expectedStreamVersion && !$streamVersion->isEqualTo($options->expectedStreamVersion)) {
            throw new ConcurrencyException($streamId, $options->expectedStreamVersion, $streamVersion);
        }

        $versionAccumulator = $streamVersion->toInt();

        $appendedEvents = [];

        /** @var EventDescriptorInterface $descriptor */
        foreach ($eventDescriptors as $descriptor) {
            $versionAccumulator++;

            // Add recorded at metadata.
            $metadata = new MutableEventMetadata($descriptor->getEventMetadata()->toArray());
            $recordedAt = $this->clock->now();
            $metadata->putValue('recordedAt', $recordedAt);

            $metadata->putValue('event_store', [
                'id' => self::EVENT_STORE_IDENTIFIER,
                'version' => self::EVENT_STORE_VERSION,
            ]);

            $sequenceNumber = EventSequenceNumber::fromInt(\count($this->events));
            $event = RecordedEventDescriptor::fromEventDescriptor(
                $descriptor,
                $streamId,
                EventStreamVersion::fromInt($versionAccumulator),
                $sequenceNumber,
                $recordedAt
            );
            $this->events[] = $event;
            $appendedEvents[] = $event;
        }

        // Call Subscribers
        foreach ($appendedEvents as $event) {
            $streamIdStr = (string) $streamId;
            if (!\array_key_exists($streamIdStr, $this->subscribers)) {
                continue;
            }

            foreach ($this->subscribers[$streamIdStr] as $subscriber) {
                $subscriber->onEvent($this, $event);
            }
        }
    }

    public function readStream(EventStreamId $streamId, ReadStreamOptions $options): StreamedEventCollectionInterface
    {
        $isGlobalStream = $streamId->isEqualTo(self::getGlobalStreamId());

        if (!$isGlobalStream && !$this->streamExists($streamId)) {
            throw new EventStreamNotFoundException($streamId);
        }

        $events = $options->direction->isEqualTo(ReadStreamDirection::BACKWARD()) ? array_reverse($this->events) : $this->events;

        $self = $this;
        $events = array_filter($events, static function (RecordedEventDescriptor $e) use ($self, $streamId, $isGlobalStream, $options) {
            if (!$isGlobalStream && !$e->getStreamId()->isEqualTo($streamId)) {
                return false;
            }

            $eventPosition = $isGlobalStream ?
                $e->getSequenceNumber()->toInt() :
                $e->getStreamVersion()->toInt()
            ;

            $readPosition = $options->position;
            if ($readPosition === ReadStreamOptions::POSITION_END) {
                $stream = $self->getStream($streamId);
                $readPosition = $isGlobalStream ? \count($self->events) : $stream->getVersion()->toInt() + 1;
            }

            if ($options->direction->isEqualTo(ReadStreamDirection::FORWARD())) {
                if ($eventPosition <= $readPosition) {
                    return false;
                }
            } else {
                if ($eventPosition >= $readPosition) {
                    return false;
                }
            }

            return true;
        });

        if ($options->maxCount) {
            $events = \array_slice($events, 0, $options->maxCount);
        }

        return new StreamedEventCollection($streamId, $events);
    }

    public function getStream(EventStreamId $streamId): ?EventStreamInterface
    {
        $versions = array_map(static function (RecordedEventDescriptor $e) use ($streamId) {
            if ($e->getStreamId()->isEqualTo($streamId)) {
                return $e->getStreamVersion()->toInt();
            }

            return null;
        }, $this->events);

        $versions = array_filter($versions, static function ($v) {
            return $v !== null;
        });

        if (!$versions) {
            return null;
        }

        return new EventStream($streamId, EventStreamVersion::fromInt(max($versions)));
    }

    public function streamExists(EventStreamId $streamId): bool
    {
        return $this->getStream($streamId) !== null;
    }

    public function subscribeToStream(EventStreamId $streamId, EventStoreSubscriberInterface $subscriber): void
    {
        $streamIdStr = (string) $streamId;
        if (!\array_key_exists($streamIdStr, $this->subscribers)) {
            $this->subscribers[$streamIdStr] = [];
        }

        $this->subscribers[$streamIdStr][] = $subscriber;
    }

    public function clear()
    {
        $this->events = [];
        $this->subscribers = [];
    }
}
