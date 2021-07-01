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

    /**
     * @param DateTime|null $dateTime if null will default to the current date time of the system
     */
    public function __construct(?DateTime $dateTime = null, string $timeZone = DateTime::DEFAULT_SYSTEM_TIME_ZONE)
    {
        date_default_timezone_set($timeZone);
        $this->dateTime = $dateTime ?? new DateTime();
    }

    /**
     * Changes the date of this clock.
     */
    public function changeDate(DateTime $dateTime): void
    {
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
