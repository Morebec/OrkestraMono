<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventProcessor\AbstractFilteringEventPublisherDecorator;
use Morebec\Orkestra\EventSourcing\EventProcessor\EventPublisherInterface;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use PHPUnit\Framework\TestCase;

class AbstractFilteringEventPublisherDecoratorTest extends TestCase
{
    public function testPublishEvent(): void
    {
        $publisher = $this->getMockBuilder(EventPublisherInterface::class)->getMock();
        $eventDescriptor = $this->getMockBuilder(RecordedEventDescriptor::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        // test keep
        $filter = $this->getMockForAbstractClass(AbstractFilteringEventPublisherDecorator::class, [$publisher]);
        $filter->method('filterEvent')->willReturn(true);
        $publisher->expects($this->once())->method('publishEvent');

        $filter->publishEvent($eventDescriptor);

        // test discard
        $filter = $this->getMockForAbstractClass(AbstractFilteringEventPublisherDecorator::class, [$publisher]);
        $filter->method('filterEvent')->willReturn(false);
        $publisher->expects($this->never())->method('publishEvent');

        $filter->publishEvent($eventDescriptor);
    }
}
