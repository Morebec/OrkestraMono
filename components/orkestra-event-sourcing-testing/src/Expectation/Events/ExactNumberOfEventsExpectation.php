<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation\Events;

use Morebec\Orkestra\EventSourcing\Testing\Expectation\TestStageExpectationInterface;
use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class ExactNumberOfEventsExpectation implements TestStageExpectationInterface
{
    private int $expectedNbEvents;

    public function __construct(int $expectedNbEvents)
    {
        $this->expectedNbEvents = $expectedNbEvents;
    }

    public function check(TestStageIntentExecutionResultInterface $execution): void
    {
        $events = $execution->getStage()->getRecordedEvents();
        $nbActualEvents = \count($events);
        try {
            TestCase::assertCount($this->expectedNbEvents, $events, "Failed asserting that exactly $this->expectedNbEvents events were recorded; $nbActualEvents events recorded.");
        } catch (ExpectationFailedException|Exception $e) {
            dump(array_map(static fn ($e) => (string) $e->getEventType(), $events));
            throw $e;
        }
    }
}
