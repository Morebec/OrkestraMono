<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptorInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventMetadata;
use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\StreamedEventCollection;
use PHPUnit\Framework\TestCase;

class StreamedEventCollectionTest extends TestCase
{
    public function testGetEventStreamId(): void
    {
        $streamId = EventStreamId::fromString('test');
        $collection = new StreamedEventCollection($streamId, []);
        $this->assertEquals($streamId, $collection->getEventStreamId());
    }

    public function testGetFirst(): void
    {
        $streamId = EventStreamId::fromString('test');

        $event1 = new RecordedEventDescriptor(
            EventId::fromString('evt1'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $event2 = new RecordedEventDescriptor(
            EventId::fromString('evt2'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $collection = new StreamedEventCollection($streamId, [$event1, $event2]);
        $this->assertEquals($event1, $collection->getFirst());
    }

    public function testGetCount(): void
    {
        $streamId = EventStreamId::fromString('test');

        $event1 = new RecordedEventDescriptor(
            EventId::fromString('evt1'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $event2 = new RecordedEventDescriptor(
            EventId::fromString('evt2'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $collection = new StreamedEventCollection($streamId, [$event1, $event2]);
        $this->assertEquals(2, $collection->getCount());
        $this->assertCount(2, $collection);
    }

    public function testIsEmpty(): void
    {
        $streamId = EventStreamId::fromString('test');

        $event1 = new RecordedEventDescriptor(
            EventId::fromString('evt1'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $event2 = new RecordedEventDescriptor(
            EventId::fromString('evt2'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $collection = new StreamedEventCollection($streamId, []);
        $this->assertTrue($collection->isEmpty());
        $this->assertEmpty($collection);

        $collection = new StreamedEventCollection($streamId, [$event1, $event2]);
        $this->assertFalse($collection->isEmpty());
        $this->assertNotEmpty($collection);
    }

    public function testGetLast(): void
    {
        $streamId = EventStreamId::fromString('test');

        $event1 = new RecordedEventDescriptor(
            EventId::fromString('evt1'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $event2 = new RecordedEventDescriptor(
            EventId::fromString('evt2'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $collection = new StreamedEventCollection($streamId, [$event1, $event2]);
        $this->assertEquals($event2, $collection->getLast());
    }

    public function testToArray(): void
    {
        $streamId = EventStreamId::fromString('test');

        $event1 = new RecordedEventDescriptor(
            EventId::fromString('evt1'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $event2 = new RecordedEventDescriptor(
            EventId::fromString('evt2'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $collection = new StreamedEventCollection($streamId, [$event1, $event2]);

        $this->assertEquals([$event1, $event2], $collection->toArray());
    }

    public function testIteration(): void
    {
        $streamId = EventStreamId::fromString('test');

        $event1 = new RecordedEventDescriptor(
            EventId::fromString('evt1'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $event2 = new RecordedEventDescriptor(
            EventId::fromString('evt2'),
            EventType::fromString('test_event.created'),
            new EventMetadata([
                'id' => 'evt1',
            ]),
            new EventData(['file' => __FILE__]),
            $streamId,
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(10),
            new DateTime()
        );

        $collection = new StreamedEventCollection($streamId, [$event1, $event2]);

        $countedEvents = 0;
        foreach ($collection as $event) {
            $this->assertInstanceOf(EventDescriptorInterface::class, $event);
            $countedEvents++;
        }

        $this->assertEquals(2, $countedEvents);

        // Try again so reset is checked
        $countedEvents = 0;
        foreach ($collection as $event) {
            $countedEvents++;
        }

        $this->assertEquals(2, $countedEvents);
    }
}
