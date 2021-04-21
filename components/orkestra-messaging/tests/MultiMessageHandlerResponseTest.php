<?php

namespace Tests\Morebec\Orkestra\Messaging;

use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MultiMessageHandlerResponse;
use PHPUnit\Framework\TestCase;

class MultiMessageHandlerResponseTest extends TestCase
{
    public function testConstruct(): void
    {
        $responses = [
            new MessageHandlerResponse('handler_failed', MessageBusResponseStatusCode::FAILED(), 'failure_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::SUCCEEDED(), 'success_payload'),
        ];

        $response = new MultiMessageHandlerResponse($responses);
        // SHOULD NOT THROW EXCEPTION

        $this->expectException(\InvalidArgumentException::class);
        $responses = [
            new MessageHandlerResponse('handler_failed', MessageBusResponseStatusCode::FAILED(), 'failure_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::SUCCEEDED(), 'success_payload'),
            'INVALID_RESPONSE_TYPE',
        ];

        new MultiMessageHandlerResponse($responses);
    }

    public function testConstructWithEmptyArrayResponsesThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $multiResponse = new MultiMessageHandlerResponse([]);
    }

    public function testConstructWithSingleResponseThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $multiResponse = new MultiMessageHandlerResponse([
            new MessageHandlerResponse('handler_failed', MessageBusResponseStatusCode::FAILED(), 'failure_payload'),
        ]);
    }

    public function testHasResponseWithStatus(): void
    {
        $responses = [
            new MessageHandlerResponse('handler_failed', MessageBusResponseStatusCode::FAILED(), 'failure_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::SUCCEEDED(), 'success_payload'),
        ];

        $multiResponse = new MultiMessageHandlerResponse($responses);

        $this->assertTrue($multiResponse->hasResponseWithStatus(MessageBusResponseStatusCode::FAILED()));
        $this->assertTrue($multiResponse->hasResponseWithStatus(MessageBusResponseStatusCode::SUCCEEDED()));
        $this->assertFalse($multiResponse->hasResponseWithStatus(MessageBusResponseStatusCode::ACCEPTED()));
    }

    public function testGetHandlerResponses(): void
    {
        $responses = [
            new MessageHandlerResponse('handler_failed', MessageBusResponseStatusCode::FAILED(), 'failure_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::SUCCEEDED(), 'success_payload'),
        ];

        $multiResponse = new MultiMessageHandlerResponse($responses);

        $this->assertCount(2, $multiResponse->getHandlerResponses());
    }

    public function testIsFailure(): void
    {
        $responses = [
            new MessageHandlerResponse('handler_failed', MessageBusResponseStatusCode::FAILED(), 'failure_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::SUCCEEDED(), 'success_payload'),
        ];

        $multiResponse = new MultiMessageHandlerResponse($responses);

        $this->assertTrue($multiResponse->isFailure());
    }

    public function testIsSuccess(): void
    {
        $responses = [
            new MessageHandlerResponse('handler_failed', MessageBusResponseStatusCode::FAILED(), 'failure_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::SUCCEEDED(), 'success_payload'),
        ];

        $multiResponse = new MultiMessageHandlerResponse($responses);

        $this->assertFalse($multiResponse->isSuccess());
    }

    public function testGetPayload(): void
    {
        $responses = [
            new MessageHandlerResponse('handler_failed', MessageBusResponseStatusCode::FAILED(), 'failure_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::SUCCEEDED(), 'success_payload'),
        ];

        $multiResponse = new MultiMessageHandlerResponse($responses);

        $this->assertIsArray($multiResponse->getPayload());
    }

    public function testGetStatusCode(): void
    {
        $responses = [
            new MessageHandlerResponse('handler_failed', MessageBusResponseStatusCode::FAILED(), 'failure_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::SUCCEEDED(), 'success_payload'),
        ];

        $multiResponse = new MultiMessageHandlerResponse($responses);
        $this->assertTrue($multiResponse->getStatusCode()->isEqualTo(MessageBusResponseStatusCode::FAILED()));

        $responses = [
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::ACCEPTED(), 'success_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::ACCEPTED(), 'success_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::SUCCEEDED(), 'success_payload'),
        ];
        $multiResponse = new MultiMessageHandlerResponse($responses);
        $this->assertTrue($multiResponse->getStatusCode()->isEqualTo(MessageBusResponseStatusCode::ACCEPTED()));

        $responses = [
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::REFUSED(), 'success_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::INVALID(), 'success_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::INVALID(), 'success_payload'),
            new MessageHandlerResponse('handler_succeeded', MessageBusResponseStatusCode::SUCCEEDED(), 'success_payload'),
        ];
        $multiResponse = new MultiMessageHandlerResponse($responses);
        $this->assertTrue($multiResponse->getStatusCode()->isEqualTo(MessageBusResponseStatusCode::INVALID()));
    }
}
