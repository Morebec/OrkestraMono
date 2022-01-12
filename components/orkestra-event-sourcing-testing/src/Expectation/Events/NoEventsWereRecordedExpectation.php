<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation\Events;

use Morebec\Orkestra\EventSourcing\Testing\Expectation\TestStageExpectationInterface;
use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;
use PHPUnit\Framework\TestCase;

/**
 * Expectation that no events were recorded.
 */
class NoEventsWereRecordedExpectation implements TestStageExpectationInterface
{
    public function check(TestStageIntentExecutionResultInterface $execution): void
    {
        $recordedEvents = $execution->getStage()->getRecordedEvents();
        TestCase::assertEmpty($recordedEvents, 'Failed asserting that no events were recorded; some events were recorded.');
    }
}
