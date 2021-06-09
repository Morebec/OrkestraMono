<?php

namespace Morebec\Orkestra\PostgreSqlTimeoutStorage;

class PostgreSqlTimeoutStorageConfiguration
{
    /** @var string */
    public $timeoutTableName = 'timeouts';

    /** @var int in milliseconds */
    public $pollingInterval = 10;
}
