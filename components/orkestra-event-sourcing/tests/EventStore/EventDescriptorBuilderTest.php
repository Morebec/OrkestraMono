<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptorBuilder;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventMetadata;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use PHPUnit\Framework\TestCase;

class EventDescriptorBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        // With type conversion
        $event = EventDescriptorBuilder::create()
            ->withId('eventId')
            ->withType('eventType')
            ->withData(['hello' => 'world'])
            ->withMetadata(['foo' => 'bar'])
            ->build()
        ;

        $this->assertEquals('eventId', $event->getEventId());
        $this->assertEquals('eventType', $event->getEventType());
        $this->assertTrue($event->getEventData()->hasKey('hello'));
        $this->assertTrue($event->getEventMetadata()->hasKey('foo'));

        $event = EventDescriptorBuilder::create()
            ->withId(EventId::fromString('eventId'))
            ->withType(EventType::fromString('eventType'))
            ->withData(new EventData(['hello' => 'world']))
            ->withMetadata(new EventMetadata(['foo' => 'bar']))
            ->build()
        ;

        $this->assertEquals('eventId', $event->getEventId());
        $this->assertEquals('eventType', $event->getEventType());
        $this->assertTrue($event->getEventData()->hasKey('hello'));
        $this->assertTrue($event->getEventMetadata()->hasKey('foo'));

        // Optional
        $event = EventDescriptorBuilder::create()
            ->withId('eventId')
            ->withType('eventType')
            ->build()
        ;
        $this->assertEquals('eventId', $event->getEventId());
        $this->assertEquals('eventType', $event->getEventType());

        // Missing Type and Id throws exception.
        $this->expectException(\InvalidArgumentException::class);
        $event = EventDescriptorBuilder::create()->build();
    }
}
