<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\EventSourcing\EventProcessor\EventPublisherInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\InMemoryEventStorePositionStorage;
use Morebec\Orkestra\EventSourcing\EventProcessor\PollingTrackingEventProcessor;
use Morebec\Orkestra\EventSourcing\EventProcessor\PollingTrackingEventProcessorOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\InMemoryEventStore;
use PHPUnit\Framework\TestCase;

class PollingTrackingEventProcessorTest extends TestCase
{
    public function testStart(): void
    {
        $processor = new PollingTrackingEventProcessor(
            $this->getMockBuilder(EventPublisherInterface::class)->getMock(),
            new InMemoryEventStore(new SystemClock()),
            new InMemoryEventStorePositionStorage(),
            (new PollingTrackingEventProcessorOptions())
                ->withName('test')
                ->withStreamId(EventStreamId::fromString('$all'))
        );

        $processor->start();
        $this->expectNotToPerformAssertions();
    }
}
