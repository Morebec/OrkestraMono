<?php

namespace Morebec\Orkestra\EventSourcing\Snapshot;

use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;

class InMemorySnapshotStore implements SnapshotStoreInterface
{
    /**
     * @var array
     */
    private $snapshots;

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

        return \array_key_exists($eventStreamIdStr, $this->snapshots) ? $this->snapshots[$eventStreamIdStr] : null;
    }
}
