<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventMetadata;
use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\StreamedEventCollection;
use Morebec\Orkestra\EventSourcing\EventStore\UpcastingEventStoreDecorator;
use Morebec\Orkestra\EventSourcing\Upcasting\AbstractMultiEventUpcaster;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcastableEventDescriptor;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcasterChain;
use PHPUnit\Framework\TestCase;

class UpcastingEventStoreDecoratorTest extends TestCase
{
    public function testAppendToStream()
    {
        $eventStore = $this->getMockBuilder(EventStoreInterface::class)->getMock();

        $streamId = EventStreamId::fromString('test-stream');
        $eventStore->method('readStream')->willReturn(new StreamedEventCollection($streamId, [
            new RecordedEventDescriptor(
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
            ),
        ]));

        $upcasterChain = new UpcasterChain([
            new class('test_event.created') extends AbstractMultiEventUpcaster {
                protected function doUpcast(UpcastableEventDescriptor $eventDescriptor): array
                {
                    // DOUBLE
                    return [$eventDescriptor, $eventDescriptor];
                }
            },
        ]);

        $store = new UpcastingEventStoreDecorator($eventStore, $upcasterChain);

        $events = $store->readStream($streamId, ReadStreamOptions::lastEvent());

        $this->assertCount(2, $events);
    }
}
