<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation;

use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;
use PHPUnit\Framework\TestCase;

class ExceptionExpectation implements TestStageExpectationInterface
{
    private string $expectedExceptionClassName;

    public function __construct(string $expectedExceptionClasName)
    {
        $this->expectedExceptionClassName = $expectedExceptionClasName;
    }

    public function check(TestStageIntentExecutionResultInterface $execution): void
    {
        if (!$execution->hasThrowable()) {
            throw new UnsatisfiedExpectationException("Failed expecting that exception \"$this->expectedExceptionClassName\" was thrown");
        }

        TestCase::assertInstanceOf($this->expectedExceptionClassName, $execution->getThrowable());
    }
}
