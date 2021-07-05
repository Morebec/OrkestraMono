<?php

namespace Morebec\Orkestra\PostgreSqlTimeoutStorage;

class PostgreSqlTimeoutStorageConfiguration
{
    public string $timeoutTableName = 'timeouts';

    /** @var int in milliseconds */
    public int $pollingInterval = 10;
}
