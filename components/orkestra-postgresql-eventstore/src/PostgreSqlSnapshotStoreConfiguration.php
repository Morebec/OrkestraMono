<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

class PostgreSqlSnapshotStoreConfiguration
{
    public string $snapshotsTableName = 'event_store_snapshots';
}
