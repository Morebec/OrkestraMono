<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation;

use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;

class NoopExpectation implements TestStageExpectationInterface
{
    public function check(TestStageIntentExecutionResultInterface $execution): void
    {
    }
}
