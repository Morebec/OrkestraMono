<?php

namespace Morebec\Orkestra\Messaging;

/**
 * Represents a response from a {@link MessageHandlerResponse} to the message bus.
 */
class MessageHandlerResponse extends AbstractMessageBusResponse
{
    /**
     * @var string
     */
    protected $handlerName;

    public function __construct(string $handlerName, MessageBusResponseStatusCode $statusCode, $payload = null)
    {
        parent::__construct($statusCode, $payload);
        $this->handlerName = $handlerName;
    }

    public function getHandlerName(): string
    {
        return $this->handlerName;
    }
}
