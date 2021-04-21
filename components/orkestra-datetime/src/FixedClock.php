<?php

namespace Morebec\Orkestra\DateTime;

/**
 * Implementation of clock always returning the same fixed date time.
 * This can be used in unit tests to have better control of time.
 */
class FixedClock implements ClockInterface
{
    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(DateTime $dateTime, string $timeZone = DateTime::DEFAULT_SYSTEM_TIME_ZONE)
    {
        date_default_timezone_set($timeZone);
        $this->dateTime = $dateTime;
    }

    public function today(): Date
    {
        return new Date($this->now());
    }

    public function yesterday(): Date
    {
        return $this->today()->subDays(1);
    }

    public function tomorrow(): Date
    {
        return $this->today()->addDay();
    }

    public function now(): DateTime
    {
        return $this->dateTime;
    }
}
