<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Precondition;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\DateTime\FixedClock;

class ClockAtDatePrecondition implements TestStagePreconditionInterface
{
    private FixedClock $clock;
    private DateTime $dateTime;

    public function __construct(FixedClock $clock, DateTime $dateTime)
    {
        $this->clock = $clock;
        $this->dateTime = $dateTime;
    }

    public function run(): void
    {
        $this->clock->changeDate($this->dateTime);
    }
}
