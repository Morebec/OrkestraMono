<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventProcessor\EventPublisherInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\SubscribedEventProcessor;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use PHPUnit\Framework\TestCase;

class SubscribedEventProcessorTest extends TestCase
{
    public function testStart(): void
    {
        $eventPublisher = $this->getMockBuilder(EventPublisherInterface::class)->getMock();

        $processor = new SubscribedEventProcessor('test', $eventPublisher);

        $eventStore = $this->getMockBuilder(EventStoreInterface::class)->getMock();
        $eventDescriptor = $this->getMockBuilder(RecordedEventDescriptor::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $eventPublisher->expects($this->once())->method('publishEvent');

        $processor->start();
        $processor->onEvent($eventStore, $eventDescriptor);
    }

    public function testStop(): void
    {
        $eventPublisher = $this->getMockBuilder(EventPublisherInterface::class)->getMock();

        $processor = new SubscribedEventProcessor('test', $eventPublisher);

        $eventStore = $this->getMockBuilder(EventStoreInterface::class)->getMock();
        $eventDescriptor = $this->getMockBuilder(RecordedEventDescriptor::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $eventPublisher->expects($this->never())->method('publishEvent');

        $processor->stop();
        $processor->onEvent($eventStore, $eventDescriptor);
    }

    public function testOnEvent(): void
    {
        $eventPublisher = $this->getMockBuilder(EventPublisherInterface::class)->getMock();

        $eventPublisher = $this->getMockBuilder(EventPublisherInterface::class)->getMock();

        $processor = new SubscribedEventProcessor('test', $eventPublisher);

        $eventStore = $this->getMockBuilder(EventStoreInterface::class)->getMock();
        $eventDescriptor = $this->getMockBuilder(RecordedEventDescriptor::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $eventPublisher->expects($this->once())->method('publishEvent');

        $processor->start();
        $processor->onEvent($eventStore, $eventDescriptor);
    }

    public function testIsRunning(): void
    {
        $eventPublisher = $this->getMockBuilder(EventPublisherInterface::class)->getMock();
        $processor = new SubscribedEventProcessor('test', $eventPublisher);

        $this->assertFalse($processor->isRunning());

        $processor->start();
        $this->assertTrue($processor->isRunning());

        $processor->stop();
        $this->assertFalse($processor->isRunning());
    }
}
