<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

class PostgreSqlEventStorePositionStorageConfiguration
{
    /** @var string */
    public $positionsTableName = 'event_processor_positions';
}
