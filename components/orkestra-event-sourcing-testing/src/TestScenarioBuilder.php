<?php

namespace Morebec\Orkestra\EventSourcing\Testing;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\Date;
use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\CompositeExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\UnsatisfiedExpectationException;
use Morebec\Orkestra\EventSourcing\Testing\Intent\NoopIntent;
use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentInterface;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\CompositePrecondition;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\FunctionPrecondition;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\TestStagePreconditionInterface;
use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandInterface;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Messaging\Domain\Query\DomainQueryInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutInterface;
use RuntimeException;
use Throwable;

class TestScenarioBuilder
{
    private TestScenario $scenario;
    private EventStoreInterface $eventStore;
    private MessageBusInterface $messageBus;
    private ClockInterface $clock;
    private MessageNormalizerInterface $messageNormalizer;

    public function __construct(
        ClockInterface $clock,
        EventStoreInterface $eventStore,
        MessageNormalizerInterface $messageNormalizer,
        MessageBusInterface $messageBus
    ) {
        $this->scenario = new TestScenario($eventStore);
        $this->eventStore = $eventStore;
        $this->messageBus = $messageBus;
        $this->messageNormalizer = $messageNormalizer;
        $this->clock = $clock;
    }

    // PRECONDITIONS //

    /**
     * Starts a stage with a given precondition.
     */
    public function given(TestStagePreconditionInterface $precondition): TestStageBuilder
    {
        return $this->createStageBuilder()->given($precondition);
    }

    /**
     * Starts as stage with a precondition from a function.
     *
     * @see FunctionPrecondition
     */
    public function givenFunc(callable $func, array $params = []): TestStageBuilder
    {
        return $this->given(FunctionPrecondition::as($func, $params));
    }

    /**
     * Starts a stage with a given stream as precondition.
     *
     * @param $streamId
     */
    public function givenEventStream($streamId): TestStageUsingStreamBuilder
    {
        if (\is_string($streamId)) {
            $streamId = EventStreamId::fromString($streamId);
        }

        return (new TestStageUsingStreamBuilder($this, $this->createStage(), $streamId))->givenEventStream($streamId);
    }

    /**
     * Starts a stage with a given date as a precondition.
     *
     * @param string|Date|DateTime $date
     *
     * @throws RuntimeException
     */
    public function givenCurrentDateIs($date): TestStageBuilder
    {
        return $this->createStageBuilder()->givenCurrentDateIs($date);
    }

    // INTENT //

    /**
     * Starts a stage with a given intent.
     */
    public function when(TestStageIntentInterface $intent): TestStageExpectationsBuilder
    {
        return $this->createStageBuilder()->when($intent);
    }

    /**
     * Starts a stage with the intent as a function.
     *
     * @see FunctionIntent
     */
    public function whenFunc(callable $func, array $params = []): TestStageExpectationsBuilder
    {
        return $this->createStageBuilder()->whenFunc($func, $params);
    }

    /**
     * Starts a stage with the intent of sending a given {@link MessageInterface} on the message bus.
     */
    public function whenMessage(MessageInterface $message, ?MessageHeaders $headers = null): TestStageMessageBusExpectationsBuilder
    {
        return $this->createStageBuilder()($message, $headers);
    }

    /**
     * Starts a stage with the intent of sending a given {@link DomainCommandInterface} on the message bus.
     */
    public function whenCommand(DomainCommandInterface $command, ?MessageHeaders $headers = null): TestStageMessageBusExpectationsBuilder
    {
        return $this->createStageBuilder()->whenCommand($command, $headers);
    }

    /**
     * Starts a stage with the intent of sending a given {@link DomainEventInterface} on the message bus.
     */
    public function whenEvent(DomainEventInterface $event, ?MessageHeaders $headers = null): TestStageMessageBusExpectationsBuilder
    {
        return $this->createStageBuilder()->whenEvent($event, $headers);
    }

    /**
     * Starts a stage with the intent of sending a given {@link DomainQueryInterface} on the message bus.
     */
    public function whenQuery(DomainQueryInterface $query, ?MessageHeaders $headers = null): TestStageMessageBusExpectationsBuilder
    {
        return $this->createStageBuilder()->whenQuery($query, $headers);
    }

    /**
     * Starts a stage with the intent of sending a given {@link TimeoutInterface} on the message bus.
     */
    public function whenTimeout(TimeoutInterface $timeout, ?MessageHeaders $headers = null): TestStageMessageBusExpectationsBuilder
    {
        return $this->createStageBuilder()->whenTimeout($timeout, $headers);
    }

    /**
     * Adds a stage to this builder.
     *
     * @return $this
     */
    public function withStage(TestStage $stage): self
    {
        $this->scenario->addStage($stage);

        return $this;
    }

    /**
     * Runs the built scenario.
     *
     * @throws Throwable
     * @throws UnsatisfiedExpectationException
     */
    public function runScenario(): void
    {
        $this->scenario->run();
    }

    public function getEventStore(): EventStoreInterface
    {
        return $this->eventStore;
    }

    public function getMessageBus(): MessageBusInterface
    {
        return $this->messageBus;
    }

    public function getClock(): ClockInterface
    {
        return $this->clock;
    }

    public function getMessageNormalizer(): MessageNormalizerInterface
    {
        return $this->messageNormalizer;
    }

    /**
     * Creates a stage and adds it to the scenario.
     */
    private function createStage(): TestStage
    {
        $stage = new TestStage(
            $this->scenario,
            $this->messageBus,
            $this->messageNormalizer,
            new CompositePrecondition(),
            new NoopIntent(),
            new CompositeExpectation()
        );
        $this->withStage($stage);

        return $stage;
    }

    private function createStageBuilder(): TestStageBuilder
    {
        return new TestStageBuilder($this, $this->createStage());
    }
}

/**
 * Given that PHP 7 does not allow declaring objects
 * inline, often it is easier to use a function.
 * The (function(){})() syntax is a bit long to type,
 * so this function serves as syntactic sugar to allow the following:
 * ```php
 *     from(static function() { });
 * ```.
 *
 * @return mixed
 */
function from(callable $callable)
{
    return $callable();
}
