<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation;

use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;

class FunctionExpectation implements TestStageExpectationInterface
{
    /**
     * @var callable
     */
    private $func;

    public function __construct(callable $func)
    {
        $this->func = $func;
    }

    public function check(TestStageIntentExecutionResultInterface $execution): void
    {
        ($this->func)($execution);
    }
}
