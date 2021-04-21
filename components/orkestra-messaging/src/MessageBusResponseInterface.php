<?php

namespace Morebec\Orkestra\Messaging;

/**
 * Represents a response pertaining to sending a message.
 * It can serve as an ACK or NACK for sending messages.
 * message bus responses should be made of primitives as much as possible for serialization purposes.
 */
interface MessageBusResponseInterface
{
    /**
     * Returns the payload associated with this Response.
     *
     * @return mixed
     */
    public function getPayload();

    /**
     * Indicates if this response represents a successful sending of a message.
     */
    public function isSuccess(): bool;

    /**
     * Indicates if this response presents a failure sending the message.
     */
    public function isFailure(): bool;

    /**
     * Returns the status code of this response.
     * TODO Change return type: BC.
     */
    public function getStatusCode(): MessageBusResponseStatusCode;
}
