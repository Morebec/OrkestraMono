<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation;

use Morebec\Orkestra\EventSourcing\Testing\Intent\MessageBusDispatchExecutionResult;
use Morebec\Orkestra\EventSourcing\Testing\Intent\TestStageIntentExecutionResultInterface;

abstract class AbstractMessageBusExpectation implements TestStageExpectationInterface
{
    public function check(TestStageIntentExecutionResultInterface $execution): void
    {
        /* @var MessageBusDispatchExecutionResult $execution */
        $this->doCheck($execution);
    }

    abstract protected function doCheck(MessageBusDispatchExecutionResult $execution): void;
}
