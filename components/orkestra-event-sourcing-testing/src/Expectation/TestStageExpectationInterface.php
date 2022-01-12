<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation;

use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;
use Throwable;

interface TestStageExpectationInterface
{
    /**
     * Checks this expectation or throws an exception if it was not satisfied.
     *
     * @throws UnsatisfiedExpectationException|Throwable
     */
    public function check(TestStageIntentExecutionResultInterface $execution): void;
}
