<?php

namespace Morebec\Orkestra\EventSourcing\Snapshot;

use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;

class InMemorySnapshotStore implements SnapshotStoreInterface
{
    private array $snapshots;

    public function __construct()
    {
        $this->snapshots = [];
    }

    public function upsert(Snapshot $snapshot): void
    {
        $this->snapshots[(string) $snapshot->getEventStreamId()] = $snapshot;
    }

    public function findByStreamId(EventStreamId $eventStreamId): ?Snapshot
    {
        $eventStreamIdStr = (string) $eventStreamId;

        return $this->snapshots[$eventStreamIdStr] ?? null;
    }
}
