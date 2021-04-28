<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventProcessor\TrackingEventProcessorProgress;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use PHPUnit\Framework\TestCase;

class TrackingEventProcessorProgressTest extends TestCase
{
    public function testGetProgressPercentage(): void
    {
        $progress = new TrackingEventProcessorProgress(
            'test-processor',
            EventStreamId::fromString('test-stream'),
            0,
            3,
            2
        );

        $this->assertEquals(75, $progress->getProgressPercentage());
    }

    public function testGetCurrentPosition(): void
    {
        $progress = new TrackingEventProcessorProgress(
            'test-processor',
            EventStreamId::fromString('test-stream'),
            0,
            1000,
            500
        );

        $this->assertEquals(500, $progress->getCurrentPosition());
    }

    public function testGetFirstPosition(): void
    {
        $progress = new TrackingEventProcessorProgress(
            'test-processor',
            EventStreamId::fromString('test-stream'),
            0,
            1000,
            500
        );

        $this->assertEquals(0, $progress->getFirstPosition());
    }

    public function testGetNumberEventsToProcess(): void
    {
        $progress = new TrackingEventProcessorProgress(
            'test-processor',
            EventStreamId::fromString('test-stream'),
            11,
            14,
            13
        );

        $this->assertEquals(1, $progress->getNumberEventsToProcess());
    }

    public function testGetLastPosition(): void
    {
        $progress = new TrackingEventProcessorProgress(
            'test-processor',
            EventStreamId::fromString('test-stream'),
            0,
            1000,
            500
        );

        $this->assertEquals(1000, $progress->getLastPosition());
    }

    public function testGetTotalNumberEvents(): void
    {
        $positions = [
            ['first' => EventStreamVersion::INITIAL_VERSION, 'last' => EventStreamVersion::INITIAL_VERSION, 'expected' => 0], // Empty stream

            ['first' => 0, 'last' => 0, 'expected' => 1],
            ['first' => 0, 'last' => 3, 'expected' => 4],

            ['first' => 2, 'last' => 4, 'expected' => 3],
        ];

        foreach ($positions as $position) {
            $progress = new TrackingEventProcessorProgress(
                'test-processor',
                EventStreamId::fromString('test-stream'),
                $position['first'],
                $position['last'],
                $position['first']
            );

            $this->assertEquals($position['expected'], $progress->getTotalNumberEvents());
        }
    }

    public function testGetNumberEventProcessed(): void
    {
        $progress = new TrackingEventProcessorProgress(
            'test-processor',
            EventStreamId::fromString('test-stream'),
            0,
            3,
            2
        );

        $this->assertEquals(3, $progress->getNumberEventProcessed());
    }

    public function testGetEventProcessorName(): void
    {
        $progress = new TrackingEventProcessorProgress(
            'test-processor',
            EventStreamId::fromString('test-stream'),
            0,
            1000,
            500
        );

        $this->assertEquals('test-processor', $progress->getEventProcessorName());
    }

    public function testGetStreamId(): void
    {
        $streamId = EventStreamId::fromString('test-stream');
        $progress = new TrackingEventProcessorProgress(
            'test-processor',
            $streamId,
            0,
            1000,
            500
        );

        $this->assertTrue($streamId->isEqualTo($progress->getStreamId()));
    }
}
