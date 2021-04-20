<?php

namespace Morebec\Orkestra\Messaging;

/**
 * Abstract implementation of a  {@link MessageBusResponseInterface} implementing the isSuccess and
 * isFailure methods according to the provided status code.
 */
class AbstractMessageBusResponse implements MessageBusResponseInterface
{
    /**
     * @var MessageBusResponseStatusCode
     */
    protected $statusCode;

    /**
     * @var mixed
     */
    protected $payload;

    public function __construct(MessageBusResponseStatusCode $statusCode, $payload = null)
    {
        $this->statusCode = $statusCode;
        $this->payload = $payload;
    }

    public function getStatusCode(): MessageBusResponseStatusCode
    {
        return $this->statusCode;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function isFailure(): bool
    {
        return $this->statusCode->isEqualTo(MessageBusResponseStatusCode::FAILED()) ||
            $this->statusCode->isEqualTo(MessageBusResponseStatusCode::REFUSED()) ||
            $this->statusCode->isEqualTo(MessageBusResponseStatusCode::INVALID());
    }

    public function isSuccess(): bool
    {
        return !$this->isFailure();
    }
}
