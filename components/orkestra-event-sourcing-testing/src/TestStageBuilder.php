<?php

namespace Morebec\Orkestra\EventSourcing\Testing;

use Morebec\Orkestra\DateTime\Date;
use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\DateTime\FixedClock;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\Testing\Intent\FunctionIntent;
use Morebec\Orkestra\EventSourcing\Testing\Intent\SendMessageToMessageBusIntent;
use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentInterface;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\ClockAtDatePrecondition;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\CompositePrecondition;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\FunctionPrecondition;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\TestStagePreconditionInterface;
use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandInterface;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Messaging\Domain\Query\DomainQueryInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutInterface;
use RuntimeException;

class TestStageBuilder
{
    protected TestScenarioBuilder $scenarioBuilder;

    protected TestStage $stage;

    protected CompositePrecondition $compositePrecondition;

    public function __construct(TestScenarioBuilder $scenarioBuilder, TestStage $stage)
    {
        $this->stage = $stage;

        /** @var CompositePrecondition $composite */
        $composite = $this->stage->getPrecondition();

        $this->compositePrecondition = $composite;

        $this->scenarioBuilder = $scenarioBuilder;
    }

    // PRECONDITIONS //

    public function given(TestStagePreconditionInterface $precondition): self
    {
        $this->compositePrecondition->addPrecondition($precondition);

        return $this;
    }

    public function givenFunc(callable $func, array $params = []): self
    {
        return $this->given(FunctionPrecondition::as($func, $params));
    }

    public function givenEventStream($streamId): TestStageUsingStreamBuilder
    {
        if (\is_string($streamId)) {
            $streamId = EventStreamId::fromString($streamId);
        }

        return new TestStageUsingStreamBuilder($this->scenarioBuilder, $this->stage, $streamId);
    }

    /**
     * @param string|Date|DateTime $date
     *
     * @throws RuntimeException
     */
    public function givenCurrentDateIs($date): self
    {
        if (\is_string($date) || $date instanceof Date) {
            $date = new DateTime($date);
        }

        $clock = $this->scenarioBuilder->getClock();

        if (!($clock instanceof FixedClock)) {
            throw new RuntimeException(sprintf('Can only set a date on a FixedClock, got "%s"', \get_class($clock)));
        }

        return $this->given(new ClockAtDatePrecondition($clock, $date));
    }

    // INTENTS //

    /**
     * Specifies the intent of the stage.
     */
    public function when(TestStageIntentInterface $intent): TestStageExpectationsBuilder
    {
        $this->stage->setIntent($intent);

        return new TestStageExpectationsBuilder($this->scenarioBuilder, $this->stage);
    }

    /**
     * Specifies the intent of the stage as being a callable.
     *
     * @see FunctionIntent
     */
    public function whenFunc(callable $func, array $params = []): TestStageExpectationsBuilder
    {
        return $this->when(FunctionIntent::as($func, $params));
    }

    /**
     * Specifies the intent of the stage as being a given message being sent to the message bus.
     */
    public function whenMessage(MessageInterface $message, ?MessageHeaders $headers = null): TestStageMessageBusExpectationsBuilder
    {
        $this->stage->setIntent(
            new SendMessageToMessageBusIntent(
                $this->scenarioBuilder->getMessageBus(),
                $message,
                $headers
            )
        );

        return new TestStageMessageBusExpectationsBuilder($this->scenarioBuilder, $this->stage);
    }

    /**
     * Specifies the intent of the stage as being a given {@link DomainCommandInterface} being sent to the message bus.
     */
    public function whenCommand(DomainCommandInterface $command, ?MessageHeaders $headers = null): TestStageMessageBusExpectationsBuilder
    {
        return $this->whenMessage($command, $headers);
    }

    /**
     * Specifies the intent of the stage as being a given {@link DomainEventInterface} being sent to the message bus.
     */
    public function whenEvent(DomainEventInterface $event, ?MessageHeaders $headers = null): TestStageMessageBusExpectationsBuilder
    {
        return $this->whenMessage($event, $headers);
    }

    /**
     * Specifies the intent of the stage as being a given {@link DomainQueryInterface} being sent to the message bus.
     */
    public function whenQuery(DomainQueryInterface $query, ?MessageHeaders $headers = null): TestStageMessageBusExpectationsBuilder
    {
        return $this->whenMessage($query, $headers);
    }

    /**
     * Specifies the intent of the stage as being a given {@link TimeoutInterface} being sent to the message bus.
     */
    public function whenTimeout(TimeoutInterface $timeout, ?MessageHeaders $headers = null): TestStageMessageBusExpectationsBuilder
    {
        return $this->whenMessage($timeout, $headers);
    }

    public function and(): TestScenarioBuilder
    {
        return $this->scenarioBuilder;
    }
}
