<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation;

use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Morebec\Orkestra\EventSourcing\Testing\Intent\MessageBusDispatchExecutionResult;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class MessageBusShouldRespondWithPayload extends AbstractMessageBusExpectation
{
    use PHPMatcherAssertions;

    /** @var mixed */
    private $expectedPayload;

    public function __construct($expectedPayload)
    {
        $this->expectedPayload = $expectedPayload;
    }

    /**
     * @throws ExpectationFailedException
     */
    protected function doCheck(MessageBusDispatchExecutionResult $execution): void
    {
        $payload = $execution->getMessageBusResponse()->getPayload();

        if ($this->expectedPayload === null) {
            TestCase::assertNull($payload);

            return;
        } else {
            TestCase::assertNotNull($payload);
        }

        $this->assertMatchesPattern($this->expectedPayload, $payload, 'Unexpected payload returned from message bus.');
    }
}
