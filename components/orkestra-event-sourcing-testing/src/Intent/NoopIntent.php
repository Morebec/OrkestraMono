<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Intent;

use Morebec\Orkestra\EventSourcing\Testing\TestStage;

class NoopIntent implements TestStageIntentInterface
{
    public function run(TestStage $stage): TestStageIntentExecutionResultInterface
    {
        return new TestStageIntentExecutionResult($stage, true, null);
    }
}
