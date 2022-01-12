<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation\Events;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\TestStageExpectationInterface;
use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;
use PHPUnit\Framework\TestCase;

/**
 * Expectation that no recorded event is of a given type.
 */
class NoEventIsOfTypeExpectation implements TestStageExpectationInterface
{
    private string $expectedEventTypeName;

    public function __construct(string $expectedEventTypeName)
    {
        $this->expectedEventTypeName = $expectedEventTypeName;
    }

    public function check(TestStageIntentExecutionResultInterface $execution): void
    {
        $recordedEvent = $execution->getStage()->getRecordedEvents();

        $eventTypeNames = array_map(static fn (RecordedEventDescriptor $evt) => (string) $evt->getEventType(), $recordedEvent);

        TestCase::assertNotContains($this->expectedEventTypeName, $eventTypeNames);
    }
}
