<?php

namespace Tests\Morebec\Orkestra\EventSourcing\Snapshot;

use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\Modeling\AbstractEventSourcedAggregateRoot;
use Morebec\Orkestra\EventSourcing\Modeling\EventSourcedAggregateRootTrait;
use Morebec\Orkestra\EventSourcing\Snapshot\Snapshot;
use Morebec\Orkestra\EventSourcing\Snapshot\Snapshotter;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use PHPUnit\Framework\TestCase;

class SnapshotterTest extends TestCase
{
    public function testTakeSnapshot(): void
    {
        $aggregateRoot = new class() extends AbstractEventSourcedAggregateRoot {
            use EventSourcedAggregateRootTrait;

            private string $fullName = 'John Doe';

            private string $emailAddress = 'john@doe.com';
        };

        $snapshotter = new Snapshotter(new ObjectNormalizer());
        $snapshot = $snapshotter->takeSnapshot(
            EventStreamId::fromString('test-stream'),
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(0),
            $aggregateRoot
        );

        self::assertEquals(new Snapshot(
            EventStreamId::fromString('test-stream'),
            EventStreamVersion::fromInt(0),
            EventSequenceNumber::fromInt(0),
            [
                'fullName' => 'John Doe',
                'emailAddress' => 'john@doe.com',
            ]
        ), $snapshot);
    }
}
