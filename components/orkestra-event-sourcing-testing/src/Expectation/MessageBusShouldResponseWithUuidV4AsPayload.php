<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Expectation;

use Morebec\Orkestra\EventSourcing\Testing\Intent\MessageBusDispatchExecutionResult;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class MessageBusShouldResponseWithUuidV4AsPayload extends AbstractMessageBusExpectation
{
    /**
     * @throws ExpectationFailedException
     */
    protected function doCheck(MessageBusDispatchExecutionResult $execution): void
    {
        $payload = $execution->getPayload();

        if (!$payload) {
            $payload = '';
        }

        TestCase::assertMatchesRegularExpression(
            '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i',
            $payload,
            'Failed asserting response payload from message bus was Uuid V4.'
        );
    }
}
