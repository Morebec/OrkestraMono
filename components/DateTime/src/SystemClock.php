<?php

namespace Morebec\Orkestra\DateTime;

/**
 * Implementation of the system clock based on the time of the system.
 */
class SystemClock implements ClockInterface
{
    public const DEFAULT_SYSTEM_TIME_ZONE = 'UTC';

    public function __construct(string $timeZone = self::DEFAULT_SYSTEM_TIME_ZONE)
    {
        date_default_timezone_set($timeZone);
    }

    public function today(): Date
    {
        return new Date($this->now());
    }

    public function yesterday(): Date
    {
        return new Date($this->now()->subDay());
    }

    public function tomorrow(): Date
    {
        return new Date($this->now()->addDay());
    }

    public function now(): DateTime
    {
        return new DateTime();
    }
}
