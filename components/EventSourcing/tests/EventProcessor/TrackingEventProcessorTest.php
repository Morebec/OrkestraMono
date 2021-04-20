<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\EventSourcing\EventProcessor\EventPublisherInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\EventStorePositionStorageInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\InMemoryEventStorePositionStorage;
use Morebec\Orkestra\EventSourcing\EventProcessor\TrackingEventProcessor;
use Morebec\Orkestra\EventSourcing\EventProcessor\TrackingEventProcessorOptions;
use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\InMemoryEventStore;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\StreamedEventCollection;
use PHPUnit\Framework\TestCase;

class TrackingEventProcessorTest extends TestCase
{
    public function testIsRunning(): void
    {
        $eventPublisher = $this->getMockBuilder(EventPublisherInterface::class)->getMock();
        $eventStore = $this->getMockBuilder(EventStoreInterface::class)->getMock();
        $eventStore->method('readStream')->willReturn(new StreamedEventCollection(EventStreamId::fromString('test'), []));

        $storage = $this->getMockBuilder(EventStorePositionStorageInterface::class)->getMock();
        $storage->method('get')->willReturn(0);

        $options = (new TrackingEventProcessorOptions())
            ->withName('test')
            ->withStreamId(EventStreamId::fromString('test'))
        ;

        $processor = new TrackingEventProcessor($eventPublisher, $eventStore, $storage, $options);

        $this->assertFalse($processor->isRunning());

        $processor->start();
        // We assert false because the tracking event processor automatically stops when it is finished.
        $this->assertFalse($processor->isRunning());
    }

    public function testStart(): void
    {
        $streamId = EventStreamId::fromString('test');

        $eventPublisher = $this->getMockBuilder(EventPublisherInterface::class)->getMock();

        $eventStore = new InMemoryEventStore(new SystemClock());
        $eventStore->appendToStream($streamId, [
            $this->getMockBuilder(RecordedEventDescriptor::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(RecordedEventDescriptor::class)->disableOriginalConstructor()->getMock(),
        ], AppendStreamOptions::append());

        $storage = new InMemoryEventStorePositionStorage();

        $options = (new TrackingEventProcessorOptions())
            ->withName('test')
            ->withStreamId($streamId)
        ;

        $processor = new TrackingEventProcessor($eventPublisher, $eventStore, $storage, $options);

        $this->assertFalse($processor->isRunning());

        $eventPublisher->expects($this->exactly(2))->method('publishEvent');
        $processor->start();
    }

    public function testStop(): void
    {
        $streamId = EventStreamId::fromString('test');

        $eventPublisher = $this->getMockBuilder(EventPublisherInterface::class)->getMock();

        $eventStore = new InMemoryEventStore(new SystemClock());
        $eventStore->appendToStream($streamId, [
            $this->getMockBuilder(RecordedEventDescriptor::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(RecordedEventDescriptor::class)->disableOriginalConstructor()->getMock(),
        ], AppendStreamOptions::append());

        $storage = new InMemoryEventStorePositionStorage();

        $options = (new TrackingEventProcessorOptions())
            ->withName('test')
            ->withStreamId($streamId)
        ;

        $processor = new TrackingEventProcessor($eventPublisher, $eventStore, $storage, $options);

        $eventPublisher->method('publishEvent')->willReturnCallback(static function (RecordedEventDescriptor $eventDescriptor) use ($processor) {
            $processor->stop();
        });

        $this->assertFalse($processor->isRunning());

        $eventPublisher->expects($this->exactly(2))->method('publishEvent');
        $processor->start();
    }

    public function testReset(): void
    {
        $eventPublisher = $this->getMockBuilder(EventPublisherInterface::class)->getMock();
        $eventStore = $this->getMockBuilder(EventStoreInterface::class)->getMock();

        $storage = $this->getMockBuilder(EventStorePositionStorageInterface::class)->getMock();
        $storage->method('get')->willReturn(0);

        $options = (new TrackingEventProcessorOptions())
            ->withName('test')
            ->withStreamId(EventStreamId::fromString('test'))
        ;

        $processor = new TrackingEventProcessor($eventPublisher, $eventStore, $storage, $options);

        $storage->expects($this->once())->method('reset');

        $processor->reset();
    }
}
