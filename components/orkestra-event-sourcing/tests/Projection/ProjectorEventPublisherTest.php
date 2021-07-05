<?php

namespace Tests\Morebec\Orkestra\EventSourcing\Projection;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\Projection\ProjectorEventPublisher;
use Morebec\Orkestra\EventSourcing\Projection\ProjectorInterface;
use PHPUnit\Framework\TestCase;

class ProjectorEventPublisherTest extends TestCase
{
    public function testPublishEvent(): void
    {
        $projector = $this->getMockBuilder(ProjectorInterface::class)->getMock();
        $publisher = new ProjectorEventPublisher($projector);
        $event = $this->getMockBuilder(RecordedEventDescriptor::class)->disableOriginalConstructor()->getMock();

        $projector->expects($this->once())->method('project');
        $publisher->publishEvent($event);
    }
}
