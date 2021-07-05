<?php

namespace Tests\Morebec\Orkestra\EventSourcing\Snapshot;

use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\Snapshot\Snapshot;
use PHPUnit\Framework\TestCase;

class SnapshotTest extends TestCase
{
    public function testGetState(): void
    {
        $snapshot = new Snapshot(
            EventStreamId::fromString('test-stream'),
            EventStreamVersion::initial(),
            EventSequenceNumber::fromInt(0),
            ['field' => 'value']
        );

        self::assertEquals(['field' => 'value'], $snapshot->getState());
    }

    public function testGetSequenceNumber(): void
    {
        $snapshot = new Snapshot(
            EventStreamId::fromString('test-stream'),
            EventStreamVersion::initial(),
            EventSequenceNumber::fromInt(0),
            []
        );
        self::assertTrue($snapshot->getSequenceNumber()->isEqualTo(EventSequenceNumber::fromInt(0)));
    }

    public function testGetStreamVersion(): void
    {
        $snapshot = new Snapshot(
            EventStreamId::fromString('test-stream'),
            EventStreamVersion::initial(),
            EventSequenceNumber::fromInt(0),
            []
        );
        self::assertTrue($snapshot->getStreamVersion()->isEqualTo(EventStreamVersion::initial()));
    }

    public function testGetEventStreamId(): void
    {
        $snapshot = new Snapshot(
            EventStreamId::fromString('test-stream'),
            EventStreamVersion::initial(),
            EventSequenceNumber::fromInt(0),
            []
        );
        self::assertTrue($snapshot->getEventStreamId()->isEqualTo(EventStreamId::fromString('test-stream')));
    }
}
