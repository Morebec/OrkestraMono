<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Intent;

use Morebec\Orkestra\EventSourcing\Testing\TestStage;

interface TestStageIntentInterface
{
    /**
     * Executes this intent for a given stage.
     */
    public function run(TestStage $stage): TestStageIntentExecutionResultInterface;
}
