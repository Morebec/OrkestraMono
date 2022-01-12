<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Intent;

use Morebec\Orkestra\EventSourcing\Testing\TestScenario;
use Morebec\Orkestra\EventSourcing\Testing\TestStage;
use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Throwable;

class MessageBusDispatchExecutionResult implements TestStageIntentExecutionResultInterface
{
    private MessageInterface $message;
    private MessageHeaders $messageHeaders;
    private MessageBusResponseInterface $messageBusResponse;
    private TestStage $stage;

    public function __construct(
        TestStage $stage,
        MessageInterface $message,
        MessageHeaders $messageHeaders,
        MessageBusResponseInterface $messageBusResponse
    ) {
        $this->stage = $stage;
        $this->message = $message;
        $this->messageHeaders = $messageHeaders;
        $this->messageBusResponse = $messageBusResponse;
    }

    public function isFailure(): bool
    {
        return $this->messageBusResponse->isFailure();
    }

    public function isSuccess(): bool
    {
        return $this->messageBusResponse->isSuccess();
    }

    public function getThrowable(): ?Throwable
    {
        return $this->messageBusResponse->isFailure() ? $this->messageBusResponse->getPayload() : null;
    }

    public function hasThrowable(): bool
    {
        return $this->getThrowable() !== null;
    }

    public function getPayload()
    {
        return $this->messageBusResponse->getPayload();
    }

    public function getMessageBusResponse(): MessageBusResponseInterface
    {
        return $this->messageBusResponse;
    }

    public function getMessageBusResponseStatusCode(): MessageBusResponseStatusCode
    {
        return $this->messageBusResponse->getStatusCode();
    }

    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    public function getMessageHeaders(): MessageHeaders
    {
        return $this->messageHeaders;
    }

    public function getScenario(): TestScenario
    {
        return $this->stage->getScenario();
    }

    public function getStage(): TestStage
    {
        return $this->stage;
    }
}
