<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Intent;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\DateTime\FixedClock;
use Morebec\Orkestra\EventSourcing\Testing\TestStage;

class SetClockDateTimeIntent implements TestStageIntentInterface
{
    private FixedClock $clock;
    private DateTime $dateTime;

    public function __construct(FixedClock $clock, DateTime $dateTime)
    {
        $this->clock = $clock;
        $this->dateTime = $dateTime;
    }

    public function run(TestStage $stage): TestStageIntentExecutionResultInterface
    {
        $this->clock->changeDate($this->dateTime);

        return new TestStageIntentExecutionResult($stage, true, null);
    }
}
