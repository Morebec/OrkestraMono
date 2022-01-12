<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation\Events;

use Morebec\Orkestra\EventSourcing\Testing\Expectation\TestStageExpectationInterface;
use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;
use PHPUnit\Framework\TestCase;

/**
 * Expects that some events were recorded.
 */
class SomeEventsWereRecordedExpectation implements TestStageExpectationInterface
{
    public function check(TestStageIntentExecutionResultInterface $execution): void
    {
        TestCase::assertNotEmpty($execution->getStage()->getRecordedEvents(), 'Failed asserting that some events were recorded; no events were recorded');
    }
}
