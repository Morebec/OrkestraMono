<?php

namespace Morebec\Orkestra\PostgreSqlTimerStorage;

class PostgreSqlTimerStorageConfiguration
{
    /** @var string */
    public $timerTableName = 'timers';

    /** @var int in milliseconds */
    public $pollingInterval = 10;
}
