<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

class PostgreSqlEventStorePositionStorageConfiguration
{
    public string $positionsTableName = 'event_processor_positions';
}
