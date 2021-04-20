<?php

namespace Morebec\Orkestra\Messaging\Context;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Represents the current execution context of the message bus.
 * It allows to track the correlation and causation of messages as well as any other message metadata.
 * It can be accessed with the {@link MessageBusContextProvider}.
 */
class MessageBusContext
{
    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @var MessageHeaders
     */
    private $messageHeaders;

    public function __construct(
        MessageInterface $message,
        MessageHeaders $messageHeaders
    ) {
        $this->message = $message;
        $this->messageHeaders = $messageHeaders;
    }

    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    public function getMessageHeaders(): MessageHeaders
    {
        return $this->messageHeaders;
    }

    /**
     * Returns the ID of the message.
     */
    public function getMessageId(): string
    {
        return $this->messageHeaders->get(MessageHeaders::MESSAGE_ID);
    }

    /**
     * Returns the DateTime at which a message was sent to the bus.
     */
    public function getMessageSentAt(): DateTime
    {
        return DateTime::createFromFormat('U.u', (string) $this->messageHeaders->get(MessageHeaders::SENT_AT));
    }

    /**
     * Returns the type of the message/.
     */
    public function getMessageType(): string
    {
        return $this->messageHeaders->get(MessageHeaders::MESSAGE_TYPE_NAME);
    }

    /**
     * Returns the CorrelationId of the message.
     */
    public function getCorrelationId(): string
    {
        return $this->messageHeaders->get(MessageHeaders::CORRELATION_ID);
    }

    /**
     * Returns the causation ID of the message.
     */
    public function getCausationId(): ?string
    {
        return $this->messageHeaders->get(MessageHeaders::CAUSATION_ID);
    }
}
