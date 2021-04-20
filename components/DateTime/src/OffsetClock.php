<?php

namespace Morebec\Orkestra\DateTime;

/**
 * Clock that returns the date time from a specific defined offset at creation as if it were the current time.
 * This is useful when we have to reverse in time or fast-forward in the future but still keep the clock running as
 * the actual time passes.
 */
class OffsetClock implements ClockInterface
{
    /**
     * @var DateTime
     */
    private $dateTime;

    private $offsetInterval;

    public function __construct(DateTime $startingDateTime, string $timeZone = DateTime::DEFAULT_SYSTEM_TIME_ZONE)
    {
        date_default_timezone_set($timeZone);
        $this->dateTime = $startingDateTime;

        $realTimeNow = new DateTime();

        $this->offsetInterval = $realTimeNow->diff($startingDateTime);
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
        return $this->today()->addDay(1);
    }

    public function now(): DateTime
    {
        $realtimeNow = new DateTime();

        return $realtimeNow->add($this->offsetInterval);
    }
}
