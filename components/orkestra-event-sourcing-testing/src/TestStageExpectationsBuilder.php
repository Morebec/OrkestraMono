<?php

namespace Morebec\Orkestra\EventSourcing\Testing;

use Morebec\Orkestra\EventSourcing\Testing\Expectation\CompositeExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\Events\NoEventsWereRecordedExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\Events\SomeEventsWereRecordedExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\ExceptionExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\FunctionExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\TestStageExpectationInterface;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\UnsatisfiedExpectationException;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Throwable;

class TestStageExpectationsBuilder
{
    protected TestScenarioBuilder $scenarioBuilder;
    protected TestStage $stage;
    protected CompositeExpectation $composite;

    public function __construct(TestScenarioBuilder $scenarioBuilder, TestStage $stage)
    {
        $this->scenarioBuilder = $scenarioBuilder;
        $this->stage = $stage;
        $this->composite = new CompositeExpectation();
        $this->stage->setExpectation($this->composite);
    }

    /**
     * Adds an expectation to the stage.
     *
     * @return $this
     */
    public function expect(TestStageExpectationInterface $expectation): self
    {
        $this->composite->addExpectation($expectation);

        return $this;
    }

    /**
     * Adds an expectation to the stage as being a given callable.
     *
     * @see FunctionExpectation
     *
     * @return $this
     */
    public function expectThat(callable $callable): self
    {
        return $this->expect(new FunctionExpectation($callable));
    }

    /**
     * Adds an expectation to the stage that a given exception should be returned.
     *
     * @param class-string $exceptionClassName
     *
     * @return $this
     */
    public function exceptionShouldBeThrown(string $exceptionClassName): self
    {
        return $this->expect(new ExceptionExpectation($exceptionClassName));
    }

    /**
     * Allows to specify a new stage.
     */
    public function and(): TestScenarioBuilder
    {
        return $this->scenarioBuilder;
    }

    /**
     * Allows to run the scenario as defined up until this point.
     *
     * @throws Throwable
     * @throws UnsatisfiedExpectationException
     */
    public function runScenario(): void
    {
        $this->scenarioBuilder->runScenario();
    }

    /**
     * Adds an expectation that some events should be recorded during the stage.
     */
    public function expectEvents(): TestStageEventsExpectationsBuilder
    {
        $this->expect(new SomeEventsWereRecordedExpectation());

        return new TestStageEventsExpectationsBuilder($this->scenarioBuilder, $this->stage, $this->composite);
    }

    /**
     * Allows specifying that a single event was expected.
     */
    public function expectSingleEventSameAs(DomainEventInterface $event): TestScenarioBuilder
    {
        $this->expectEvents()
            ->exactly(1)
            ->inThisOrder()
            ->sameAs($event)
        ;

        return $this->and();
    }

    /**
     * Adds an expectation that no events should be recorded during the stage.
     */
    public function expectNoEvents(): self
    {
        return $this->expect(new NoEventsWereRecordedExpectation());
    }
}
