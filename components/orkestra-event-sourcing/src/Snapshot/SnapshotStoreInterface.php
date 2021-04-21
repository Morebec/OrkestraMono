<?php

namespace Morebec\Orkestra\EventSourcing\Snapshot;

use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;

/**
 * contains all snapshots.
 */
interface SnapshotStoreInterface
{
    /**
     * Upserts a snapshot.
     */
    public function upsert(Snapshot $snapshot): void;

    /**
     * Returns a snapshot by its ID.
     */
    public function findByStreamId(EventStreamId $eventStreamId): ?Snapshot;
}
