<?php

namespace Morebec\Orkestra\EventSourcing\Snapshot;

use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;

/**
 * contains all snapshots.
 */
interface SnapshotStoreInterface
{
    /**
     * Upserts a snapshot for a given stream.
     */
    public function upsert(Snapshot $snapshot): void;

    /**
     * Returns a snapshot by its stream ID.
     */
    public function findByStreamId(EventStreamId $eventStreamId): ?Snapshot;
}
