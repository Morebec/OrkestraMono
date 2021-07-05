<?php

namespace Tests\Morebec\Orkestra\EventSourcing\Snapshot;

use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\Snapshot\InMemorySnapshotStore;
use Morebec\Orkestra\EventSourcing\Snapshot\Snapshot;
use PHPUnit\Framework\TestCase;

class InMemorySnapshotStoreTest extends TestCase
{
    public function testUpsert(): void
    {
        $store = new InMemorySnapshotStore();
        $store->upsert(new Snapshot(
            EventStreamId::fromString('test-stream'),
            EventStreamVersion::initial(),
            EventSequenceNumber::fromInt(0),
            []
        ));

        self::assertNotNull($store->findByStreamId(EventStreamId::fromString('test-stream')));
    }
}
