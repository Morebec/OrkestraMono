<?php

namespace Morebec\Orkestra\Messaging;

use Throwable;

/**
 * Represents a response pertaining to sending a message.
 * It can serve as an ACK or NACK for sending messages.
 * message bus responses should be made of primitives as much as possible for serialization purposes.
 */
interface MessageBusResponseInterface
{
    /**
     * Returns the payload associated with this Response.
     * If the response is a failure, an exception should be returned as payload.
     *
     * @return mixed|Throwable
     */
    public function getPayload();

    /**
     * Indicates if this response represents a successful sending of a message.
     */
    public function isSuccess(): bool;

    /**
     * Indicates if this response represents a failure sending the message.
     */
    public function isFailure(): bool;

    /**
     * Returns the status code of this response.
     */
    public function getStatusCode(): MessageBusResponseStatusCode;
}
