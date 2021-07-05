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
use Morebec\Orkestra\EventSourcing\Upcasting\AbstractEventTypeSpecificUpcaster;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcastableEventDescriptor;
use PHPUnit\Framework\TestCase;

class AbstractEventTypeSpecificUpcasterTest extends TestCase
{
    public function testSupports(): void
    {
        $upcaster = new class() extends AbstractEventTypeSpecificUpcaster {
            public function __construct()
            {
                parent::__construct('test_event_type');
            }

            public function upcast(UpcastableEventDescriptor $eventDescriptor): array
            {
                return [];
            }
        };

        $event = new UpcastableEventDescriptor(
            EventId::generate(),
            EventType::fromString('test_event_type'),
            new EventMetadata(),
            new EventData(),
            EventStreamId::fromString('unit_test'),
            EventStreamVersion::fromInt(50),
            EventSequenceNumber::fromInt(50),
            new DateTime('2020-05-05')
        );

        self::assertTrue($upcaster->supports($event));

        $notUpcastableEvent = new UpcastableEventDescriptor(
            EventId::generate(),
            EventType::fromString('not_supported_by_upcaster'),
            new EventMetadata(),
            new EventData(),
            EventStreamId::fromString('unit_test'),
            EventStreamVersion::fromInt(50),
            EventSequenceNumber::fromInt(50),
            new DateTime('2020-05-05')
        );

        self::assertFalse($upcaster->supports($notUpcastableEvent));
    }
}
