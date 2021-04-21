<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\EventSourcing\EventProcessor\EventPublisherInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\InMemoryEventStorePositionStorage;
use Morebec\Orkestra\EventSourcing\EventProcessor\SubscribedTrackingEventProcessor;
use Morebec\Orkestra\EventSourcing\EventProcessor\SubscribedTrackingEventProcessorOptions;
use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\InMemoryEventStore;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use PHPUnit\Framework\TestCase;

class SubscribedTrackingEventProcessorTest extends TestCase
{
    public function testOnEvent(): void
    {
        $streamId = EventStreamId::fromString('test_stream');

        // Prepare event store with an event in it.
        $eventStore = new InMemoryEventStore(new SystemClock());
        $eventStore->appendToStream(
            $streamId,
            [
                 $this->getMockBuilder(RecordedEventDescriptor::class)
                ->disableOriginalConstructor()
                ->getMock(),
            ],
            AppendStreamOptions::append()
        );

        // Prepare Processor
        $eventPublisher = $this->getMockBuilder(EventPublisherInterface::class)->getMock();

        $storage = new InMemoryEventStorePositionStorage();

        $options = (new SubscribedTrackingEventProcessorOptions())
            ->withStreamId($streamId)
            ->withName('test')
        ;
        $processor = new SubscribedTrackingEventProcessor($eventPublisher, $eventStore, $storage, $options);

        // We expect a first time, because there is one event in the stream.
        // And we expect a second time because we will add a new event in the stream.
        $eventPublisher->expects($this->exactly(2))->method('publishEvent');

        $processor->start();
        $eventStore->appendToStream(
            $streamId,
            [
                $this->getMockBuilder(RecordedEventDescriptor::class)
                    ->disableOriginalConstructor()
                    ->getMock(),
            ],
            AppendStreamOptions::append()
        );
    }
}
