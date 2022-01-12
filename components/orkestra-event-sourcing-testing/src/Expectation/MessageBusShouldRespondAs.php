<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation;

use Morebec\Orkestra\EventSourcing\Testing\Intent\MessageBusDispatchExecutionResult;
use Throwable;

class MessageBusShouldRespondAs extends AbstractMessageBusExpectation
{
    /**
     * @var callable
     */
    private $func;

    public function __construct(callable $func)
    {
        $this->func = $func;
    }

    /**
     * @throws UnsatisfiedExpectationException|Throwable
     */
    protected function doCheck(MessageBusDispatchExecutionResult $execution): void
    {
        ($this->func)($execution);
    }
}
