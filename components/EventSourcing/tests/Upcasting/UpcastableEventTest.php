<?php

namespace Tests\Morebec\Orkestra\EventSourcing\Upcasting;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventMetadata;
use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcastableEventDescriptor;
use PHPUnit\Framework\TestCase;

class UpcastableEventTest extends TestCase
{
    public function testWithFieldRenamed(): void
    {
        $eventDescriptor = new UpcastableEventDescriptor(
            EventId::fromString('event1'),
            EventType::fromString('event.test'),
            new EventMetadata(),
            new EventData(['user' => 'test.user']),
            EventStreamId::fromString('test_stream'),
            EventStreamVersion::initial(),
            EventSequenceNumber::fromInt(0),
            new DateTime()
        );

        $eventDescriptor = $eventDescriptor->withFieldRenamed('user', 'username');

        $this->assertEquals(['username' => 'test.user'], $eventDescriptor->getEventData()->toArray());
    }

    public function testWithFieldAdd(): void
    {
        $eventDescriptor = new UpcastableEventDescriptor(
            EventId::fromString('event1'),
            EventType::fromString('event.test'),
            new EventMetadata(),
            new EventData(['username' => 'test.user']),
            EventStreamId::fromString('test_stream'),
            EventStreamVersion::initial(),
            EventSequenceNumber::fromInt(0),
            new DateTime()
        );

        $eventDescriptor = $eventDescriptor->withFieldAdded('emailAddress', 'unspecified');

        $this->assertEquals(['username' => 'test.user', 'emailAddress' => 'unspecified'], $eventDescriptor->getEventData()->toArray());
    }

    public function testWithFieldRemoved(): void
    {
        $eventDescriptor = new UpcastableEventDescriptor(
            EventId::fromString('event1'),
            EventType::fromString('event.test'),
            new EventMetadata(),
            new EventData(['username' => 'test.user']),
            EventStreamId::fromString('test_stream'),
            EventStreamVersion::initial(),
            EventSequenceNumber::fromInt(0),
            new DateTime()
        );

        $eventDescriptor = $eventDescriptor->withFieldRemoved('username');

        $this->assertEquals([], $eventDescriptor->getEventData()->toArray());
    }
}
