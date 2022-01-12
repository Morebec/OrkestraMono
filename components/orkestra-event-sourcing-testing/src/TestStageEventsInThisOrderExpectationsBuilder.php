<?php

namespace Morebec\Orkestra\EventSourcing\Testing;

use Morebec\Orkestra\EventSourcing\Testing\Expectation\CompositeExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\Events\EventAtIndexHasTypeNameExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\Events\EventAtIndexIsSameAsExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\FunctionExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\TestStageExpectationInterface;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\UnsatisfiedExpectationException;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Throwable;

class TestStageEventsInThisOrderExpectationsBuilder
{
    private TestScenarioBuilder $scenarioBuilder;
    private TestStage $stage;
    private int $expectedIndex;
    private CompositeExpectation $composite;

    public function __construct(TestScenarioBuilder $scenarioBuilder, TestStage $stage, CompositeExpectation $composite, int $expectedIndex)
    {
        $this->scenarioBuilder = $scenarioBuilder;
        $this->stage = $stage;
        $this->expectedIndex = $expectedIndex;
        $this->composite = $composite;
    }

    /**
     * Adds an expectation to this stage.
     *
     * @return $this
     */
    public function expect(TestStageExpectationInterface $expectation): self
    {
        $this->composite->addExpectation($expectation);

        return $this;
    }

    /**
     * Adds an expectation that the event at the current index as a given type name.
     *
     * @return $this
     */
    public function hasTypeName(string $expectedTypeNameOrClassName): self
    {
        if (class_exists($expectedTypeNameOrClassName)) {
            $expectedTypeNameOrClassName = $expectedTypeNameOrClassName::getTypeName();
        }

        return $this->expect(new EventAtIndexHasTypeNameExpectation($this->expectedIndex, $expectedTypeNameOrClassName));
    }

    /**
     * Allows defining the expectation for the next event in the list of recorded events.
     */
    public function nextOne(): self
    {
        return new self($this->scenarioBuilder, $this->stage, $this->composite, $this->expectedIndex + 1);
    }

    /**
     * Expects that the event at this stage is the same as another one.
     *
     * @return $this
     */
    public function sameAs(DomainEventInterface $event): self
    {
        return $this->expect(new EventAtIndexIsSameAsExpectation($this->expectedIndex, $event));
    }

    public function expectThat(callable $callable): self
    {
        return $this->expect(new FunctionExpectation($callable));
    }

    public function and(): TestScenarioBuilder
    {
        return $this->scenarioBuilder;
    }

    /**
     * Runs the scenario.
     *
     * @throws Throwable
     * @throws UnsatisfiedExpectationException
     */
    public function runScenario(): void
    {
        $this->scenarioBuilder->runScenario();
    }
}
