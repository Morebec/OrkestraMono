<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventProcessor\TrackingEventProcessorOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use PHPUnit\Framework\TestCase;

class TrackingEventProcessorOptionsTest extends TestCase
{
    public function testWithStreamId(): void
    {
        $options = new TrackingEventProcessorOptions();

        $streamId = EventStreamId::fromString('test');
        $options->withStreamId($streamId);

        $this->assertTrue($streamId->isEqualTo($options->streamId));
    }

    public function testWithName(): void
    {
        $options = new TrackingEventProcessorOptions();

        $options->withName('test');
        $this->assertEquals('test', $options->name);
    }
}
