<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation\Events;

use Coduo\PHPMatcher\PHPUnit\PHPMatcherConstraint;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\TestStageExpectationInterface;
use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use PHPUnit\Framework\TestCase;

class EventAtIndexIsSameAsExpectation implements TestStageExpectationInterface
{
    private int $expectedIndex;
    private DomainEventInterface $expectedEvent;

    public function __construct(int $expectedIndex, DomainEventInterface $expectedEvent)
    {
        $this->expectedIndex = $expectedIndex;
        $this->expectedEvent = $expectedEvent;
    }

    public function check(TestStageIntentExecutionResultInterface $execution): void
    {
        $events = $execution->getStage()->getRecordedEvents();
        TestCase::assertArrayHasKey($this->expectedIndex, $events, "Failed asserting there was an event at index $this->expectedIndex");
        TestCase::assertEquals($this->expectedEvent::getTypeName(), (string) $events[$this->expectedIndex]->getEventType());

        $actualEvent = $events[$this->expectedIndex];
        /** @noinspection PhpUndefinedMethodInspection */
        $expectedData = $this->expectedEvent->toArray();

        $actualData = $actualEvent->getEventData()->toArray();

        unset($actualData['messageTypeName']);
        TestCase::assertThat(
            json_encode($actualData, \JSON_THROW_ON_ERROR),
            new PHPMatcherConstraint(json_encode($expectedData, \JSON_THROW_ON_ERROR))
        );
    }
}
