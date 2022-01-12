<?php

namespace Morebec\Orkestra\EventSourcing\Testing;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\NoopExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\TestStageExpectationInterface;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\UnsatisfiedExpectationException;
use Morebec\Orkestra\EventSourcing\Testing\Intent\NoopIntent;
use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;
use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentInterface;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\NoopPrecondition;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\TestStagePreconditionInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Throwable;

/**
 * Represents a stage in a test.
 */
class TestStage
{
    private TestStagePreconditionInterface $precondition;

    private TestStageIntentInterface $intent;

    private TestStageExpectationInterface $expectation;

    private TestScenario $scenario;

    private EventStoreWatcher $eventStoreWatcher;

    private MessageBusInterface $messageBus;

    private MessageNormalizerInterface $messageNormalizer;

    public function __construct(
        TestScenario $scenario,
        MessageBusInterface $messageBus,
        MessageNormalizerInterface $messageNormalizer,
        ?TestStagePreconditionInterface $precondition,
        ?TestStageIntentInterface $intent,
        ?TestStageExpectationInterface $expectation
    ) {
        $this->precondition = $precondition;
        $this->intent = $intent;
        $this->expectation = $expectation;
        $this->scenario = $scenario;
        $this->eventStoreWatcher = new EventStoreWatcher($scenario->getEventStore());
        $this->messageBus = $messageBus;
        $this->messageNormalizer = $messageNormalizer;
    }

    /**
     * @param NoopPrecondition|TestStagePreconditionInterface $precondition
     */
    public function setPrecondition($precondition): void
    {
        $this->precondition = $precondition;
    }

    /**
     * @param NoopIntent|TestStageIntentInterface $intent
     */
    public function setIntent($intent): void
    {
        $this->intent = $intent;
    }

    /**
     * @param NoopExpectation|TestStageExpectationInterface $expectation
     */
    public function setExpectation($expectation): void
    {
        $this->expectation = $expectation;
    }

    /**
     * @throws UnsatisfiedExpectationException
     * @throws Throwable
     */
    public function run(): void
    {
        $this->runPrecondition();

        // Pre intent update (
        $this->eventStoreWatcher->update();

        $result = $this->runIntent();

        // Post intent update
        $events = $this->eventStoreWatcher->update();
        $this->dispatchEventsToMessageBus($events);

        // Some new events might have been published after the message bus dispatch
        $this->eventStoreWatcher->update();

        $this->checkExpectation($result);
    }

    public function getPrecondition(): ?TestStagePreconditionInterface
    {
        return $this->precondition;
    }

    public function getIntent(): ?TestStageIntentInterface
    {
        return $this->intent;
    }

    public function getExpectation(): ?TestStageExpectationInterface
    {
        return $this->expectation;
    }

    public function getScenario(): TestScenario
    {
        return $this->scenario;
    }

    /**
     * Returns a list of events that were recorded so far during this stage.
     * Note: It does not include the events that were recorded as part of the preconditions.
     *
     * @return RecordedEventDescriptor[]
     */
    public function getRecordedEvents(): array
    {
        return $this->eventStoreWatcher->getRecordedEvents();
    }

    protected function runPrecondition(): void
    {
        $this->precondition->run();
    }

    protected function runIntent(): TestStageIntentExecutionResultInterface
    {
        return $this->intent->run($this);
    }

    /**
     * @throws UnsatisfiedExpectationException
     * @throws Throwable
     */
    protected function checkExpectation(TestStageIntentExecutionResultInterface $result): void
    {
        $this->expectation->check($result);
    }

    /**
     * Dispatches the events to the message bus.
     *
     * @param RecordedEventDescriptor[] $events
     */
    protected function dispatchEventsToMessageBus(array $events): void
    {
        foreach ($events as $event) {
            $e = $this->messageNormalizer->denormalize($event->getEventData()->toArray(), $event->getEventType());
            $this->messageBus->sendMessage($e, new MessageHeaders($event->getEventMetadata()->toArray()));
        }
    }
}
