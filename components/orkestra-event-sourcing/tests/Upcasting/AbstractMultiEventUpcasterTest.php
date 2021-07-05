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
use Morebec\Orkestra\EventSourcing\Upcasting\AbstractMultiEventUpcaster;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcastableEventDescriptor;
use PHPUnit\Framework\TestCase;

class AbstractMultiEventUpcasterTest extends TestCase
{
    public function testUpcast(): void
    {
        $upcaster = new class() extends AbstractMultiEventUpcaster {
            public function __construct()
            {
                parent::__construct('test_event_type');
            }

            protected function doUpcast(UpcastableEventDescriptor $event): array
            {
                return [
                    $event->withFieldAdded('test1', true),

                    $event->withFieldAdded('test2', true),
                ];
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

        self::assertCount(2, $result);
        self::assertEquals(['test1' => true], $result[0]->getEventData()->toArray());
        self::assertEquals(['test2' => true], $result[1]->getEventData()->toArray());
    }
}
