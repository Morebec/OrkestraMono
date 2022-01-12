<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation;

use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;

class CompositeExpectation implements TestStageExpectationInterface
{
    /** @var TestStageExpectationInterface[] */
    private array $expectations;

    public function __construct(array $expectations = [])
    {
        $this->expectations = [];
        foreach ($expectations as $expectation) {
            $this->addExpectation($expectation);
        }
    }

    public function addExpectation(TestStageExpectationInterface $expectation): self
    {
        $this->expectations[] = $expectation;

        return $this;
    }

    public function check(TestStageIntentExecutionResultInterface $execution): void
    {
        foreach ($this->expectations as $expectation) {
            $expectation->check($execution);
        }
    }
}
