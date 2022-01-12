<?php

namespace Morebec\Orkestra\EventSourcing\Testing;

use Morebec\Orkestra\EventSourcing\Testing\Expectation\CompositeExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\Events\ExactNumberOfEventsExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\Events\NoEventIsOfTypeExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\Events\OneEventIsOfTypeExpectation;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\TestStageExpectationInterface;

class TestStageEventsExpectationsBuilder
{
    private CompositeExpectation $composite;
    private TestScenarioBuilder $scenarioBuilder;
    private TestStage $stage;

    public function __construct(TestScenarioBuilder $scenarioBuilder, TestStage $stage, CompositeExpectation $compositeExpectation)
    {
        $this->scenarioBuilder = $scenarioBuilder;
        $this->stage = $stage;
        $this->composite = $compositeExpectation;
    }

    /**
     * Adds an expectation to this stage.
     *
     * @param OneEventIsOfTypeExpectation $expectation
     *
     * @return TestStageEventsExpectationsBuilder
     */
    public function expect(TestStageExpectationInterface $expectation): self
    {
        $this->composite->addExpectation($expectation);

        return $this;
    }

    /**
     * Expects that one event is of a given type.
     *
     * @return TestStageEventsExpectationsBuilder
     */
    public function oneIsOfType(string $expectedEventTypeNameOrClassName): self
    {
        if (class_exists($expectedEventTypeNameOrClassName)) {
            $expectedEventTypeNameOrClassName = $expectedEventTypeNameOrClassName::getTypeName();
        }

        return $this->expect(new OneEventIsOfTypeExpectation($expectedEventTypeNameOrClassName));
    }

    /**
     * Expects that no event is of a given type.
     *
     * @return TestStageEventsExpectationsBuilder
     */
    public function noneIsOfType(string $expectedEventTypeNameOrClassName): self
    {
        if (class_exists($expectedEventTypeNameOrClassName)) {
            $expectedEventTypeNameOrClassName = $expectedEventTypeNameOrClassName::getTypeName();
        }

        return $this->expect(new NoEventIsOfTypeExpectation($expectedEventTypeNameOrClassName));
    }

    /**
     * Adds an expectation that the recorded events during the stage correspond to the ones
     * provided in this specific order.
     */
    public function inThisOrder(): TestStageEventsInThisOrderExpectationsBuilder
    {
        return new TestStageEventsInThisOrderExpectationsBuilder($this->scenarioBuilder, $this->stage, $this->composite, 0);
    }

    /**
     * Allows specifying the exact number of events expected.
     *
     * @return void
     */
    public function exactly(int $nbEventsExpected): self
    {
        return $this->expect(new ExactNumberOfEventsExpectation($nbEventsExpected));
    }
}
