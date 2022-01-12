<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation\Events;

use Morebec\Orkestra\EventSourcing\Testing\Expectation\TestStageExpectationInterface;
use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;
use PHPUnit\Framework\TestCase;

class EventAtIndexHasTypeNameExpectation implements TestStageExpectationInterface
{
    private int $expectedIndex;
    private string $expectedTypeName;

    public function __construct(int $expectedIndex, string $expectedTypeName)
    {
        $this->expectedIndex = $expectedIndex;
        $this->expectedTypeName = $expectedTypeName;
    }

    public function check(TestStageIntentExecutionResultInterface $execution): void
    {
        $events = $execution->getStage()->getRecordedEvents();
        TestCase::assertArrayHasKey($this->expectedIndex, $events, "Failed asserting there was an event at index $this->expectedIndex");

        TestCase::assertEquals($this->expectedTypeName, (string) $events[$this->expectedIndex]->getEventType());
    }
}
