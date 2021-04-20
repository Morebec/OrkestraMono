<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

/**
 * Represents the configuration of the {@link PostgreSqlEventStore}.
 */
class PostgreSqlEventStoreConfiguration
{
    /** @var string */
    public $eventsTableName = 'events';

    /** @var string */
    public $streamsTableName = 'streams';

    /** @var int length of time in milliseconds to wait for a response from the database for new event. */
    public $notifyTimeout = 10;
}
