<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

/**
 * Represents the configuration of the {@link PostgreSqlEventStore}.
 */
class PostgreSqlEventStoreConfiguration
{
    public string $eventsTableName = 'events';

    public string $streamsTableName = 'streams';

    /** @var int length of time in milliseconds to wait for a response from the database for new event. */
    public int $notifyTimeout = 10;
}
