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
use Morebec\Orkestra\EventSourcing\Upcasting\AbstractSingleEventUpcaster;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcastableEventDescriptor;
use PHPUnit\Framework\TestCase;

class AbstractSingleEventUpcasterTest extends TestCase
{
    public function testUpcast(): void
    {
        $upcaster = new class() extends AbstractSingleEventUpcaster {
            public function __construct()
            {
                parent::__construct('test_event_type');
            }

            protected function doUpcast(UpcastableEventDescriptor $event): UpcastableEventDescriptor
            {
                return $event->withFieldAdded('test', true);
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

        $result = $upcaster->upcast($event);

        self::assertCount(1, $result);
        self::assertEquals(['test' => true], $result[0]->getEventData()->toArray());
    }
}
