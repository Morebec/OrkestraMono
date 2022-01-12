<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation;

use Morebec\Orkestra\EventSourcing\Testing\Intent\MessageBusDispatchExecutionResult;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class MessageBusShouldRespondWithStatusCode extends AbstractMessageBusExpectation
{
    private MessageBusResponseStatusCode $expectedStatusCode;

    public function __construct(MessageBusResponseStatusCode $expectedStatusCode)
    {
        $this->expectedStatusCode = $expectedStatusCode;
    }

    /**
     * @throws ExpectationFailedException
     */
    protected function doCheck(MessageBusDispatchExecutionResult $execution): void
    {
        $actualStatusCode = $execution->getMessageBusResponseStatusCode();

        TestCase::assertEquals(
            $this->expectedStatusCode,
            $actualStatusCode,
            sprintf('Wrong status code received for message "%s": expected "%s" got "%s".
(Payload: "%s")',
                $execution->getMessage()::getTypeName(),
                $this->expectedStatusCode,
                $actualStatusCode,
                get_debug_type($execution->getPayload())
            ));
    }
}
